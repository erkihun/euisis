<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Positions\ArchivePositionAction;
use App\Actions\Positions\CreatePositionAction;
use App\Actions\Positions\RestorePositionAction;
use App\Actions\Positions\UpdatePositionAction;
use App\Enums\HierarchyVersionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Http\Resources\PositionResource;
use App\Models\GradeLevel;
use App\Models\HierarchyVersion;
use App\Models\Occupation;
use App\Models\Organization;
use App\Models\OrganizationEdge;
use App\Models\OrganizationUnit;
use App\Models\Position;
use App\Services\OrganizationScope\OrganizationScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    public function index(Request $request, OrganizationScopeService $organizationScopeService): Response
    {
        $this->authorize('viewAny', Position::class);

        $user = $request->user();
        $accessibleOrganizationIds = $organizationScopeService->accessibleOrganizationIds($user);

        // Build organization tree
        $orgQuery = Organization::query()
            ->with(['type:id,name_en,name_am,code'])
            ->withCount(['organizationUnits' => fn ($q) => $q->whereNull('deleted_at')])
            ->orderBy('name_en');

        if ($accessibleOrganizationIds->isNotEmpty()) {
            $orgQuery->whereIn('id', $accessibleOrganizationIds);
        }

        $organizations = $orgQuery->get([
            'id', 'organization_type_id', 'code', 'name_en', 'name_am',
            'status', 'logo_path', 'effective_from',
        ]);

        $publishedVersion = HierarchyVersion::query()
            ->where('status', HierarchyVersionStatus::Published)
            ->latest('effective_from')
            ->first(['id']);

        $hasPublishedHierarchy = $publishedVersion !== null;
        $orgMap = $organizations->keyBy('id');

        if ($hasPublishedHierarchy) {
            $edges = OrganizationEdge::query()
                ->where('hierarchy_version_id', $publishedVersion->id)
                ->get(['parent_organization_id', 'child_organization_id']);

            $childrenMap = [];
            $edgeChildIds = [];
            foreach ($edges as $edge) {
                $pid = $edge->parent_organization_id;
                $cid = $edge->child_organization_id;
                if ($orgMap->has($pid) && $orgMap->has($cid)) {
                    $childrenMap[$pid][] = $cid;
                    $edgeChildIds[] = $cid;
                }
            }
            $edgeChildIds = array_unique($edgeChildIds);

            $buildOrgNode = function (string $orgId, int $depth) use (&$buildOrgNode, $orgMap, $childrenMap): ?array {
                $org = $orgMap->get($orgId);
                if (! $org) {
                    return null;
                }
                $children = [];
                foreach ($childrenMap[$orgId] ?? [] as $childId) {
                    $child = $buildOrgNode($childId, $depth + 1);
                    if ($child !== null) {
                        $children[] = $child;
                    }
                }

                return [
                    'id' => $org->id,
                    'code' => $org->code,
                    'name_en' => $org->name_en,
                    'name_am' => $org->name_am,
                    'status' => $org->status,
                    'logo_url' => $org->logo_url,
                    'has_logo' => $org->has_logo,
                    'organization_units_count' => $org->organization_units_count,
                    'type' => $org->type ? [
                        'id' => $org->type->id,
                        'code' => $org->type->code,
                        'name_en' => $org->type->name_en,
                        'name_am' => $org->type->name_am,
                    ] : null,
                    'depth' => $depth,
                    'children' => $children,
                ];
            };

            $organizationTree = $organizations
                ->filter(fn ($org) => ! in_array($org->id, $edgeChildIds, true))
                ->map(fn ($org) => $buildOrgNode($org->id, 0))
                ->filter()
                ->values()
                ->all();
        } else {
            $organizationTree = $organizations->map(fn ($org) => [
                'id' => $org->id,
                'code' => $org->code,
                'name_en' => $org->name_en,
                'name_am' => $org->name_am,
                'status' => $org->status,
                'logo_url' => $org->logo_url,
                'has_logo' => $org->has_logo,
                'organization_units_count' => $org->organization_units_count,
                'type' => $org->type ? [
                    'id' => $org->type->id,
                    'code' => $org->type->code,
                    'name_en' => $org->type->name_en,
                    'name_am' => $org->type->name_am,
                ] : null,
                'depth' => 0,
                'children' => [],
            ])->values()->all();
        }

        $selectedOrganization = null;
        $organizationUnits = [];
        $selectedUnit = null;
        $positions = collect();

        if ($request->filled('organization_id')) {
            $orgId = $request->string('organization_id')->toString();

            if ($accessibleOrganizationIds->isNotEmpty() && ! $accessibleOrganizationIds->contains($orgId)) {
                abort(403);
            }

            $selectedOrganization = Organization::query()
                ->with(['type:id,name_en,name_am,code'])
                ->withCount(['organizationUnits' => fn ($q) => $q->whereNull('deleted_at')])
                ->find($orgId, [
                    'id', 'organization_type_id', 'code', 'name_en', 'name_am',
                    'status', 'logo_path', 'effective_from',
                ]);

            if ($selectedOrganization !== null) {
                $allUnits = OrganizationUnit::query()
                    ->where('organization_id', $orgId)
                    ->whereNull('deleted_at')
                    ->orderBy('sort_order')
                    ->orderBy('name_en')
                    ->get(['id', 'code', 'name_en', 'name_am', 'parent_unit_id']);

                $buildUnitTree = function (?string $parentId, int $depth) use (&$buildUnitTree, $allUnits): array {
                    return $allUnits
                        ->filter(fn ($u) => $u->parent_unit_id === $parentId)
                        ->map(fn ($u) => [
                            'id' => $u->id,
                            'code' => $u->code,
                            'name_en' => $u->name_en,
                            'name_am' => $u->name_am,
                            'depth' => $depth,
                            'children' => $buildUnitTree($u->id, $depth + 1),
                        ])
                        ->values()
                        ->all();
                };

                $organizationUnits = $buildUnitTree(null, 0);

                $unitId = $request->string('organization_unit_id')->toString() ?: null;
                if ($unitId) {
                    $unitModel = $allUnits->firstWhere('id', $unitId);
                    if ($unitModel) {
                        $selectedUnit = [
                            'id' => $unitModel->id,
                            'name_en' => $unitModel->name_en,
                            'name_am' => $unitModel->name_am,
                        ];
                    }
                }

                $positions = Position::query()
                    ->with('organization:id,name_en')
                    ->withCount('assignments')
                    ->where('organization_id', $orgId)
                    ->when($unitId, fn ($q) => $q->where('organization_unit_id', $unitId))
                    ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                        $search = $request->string('search')->toString();
                        $query->where(function ($nested) use ($search): void {
                            $nested->where('job_position_code', 'like', "%{$search}%")
                                ->orWhere('title_en', 'like', "%{$search}%")
                                ->orWhere('title_am', 'like', "%{$search}%");
                        });
                    })
                    ->when($request->string('job_family')->toString() !== '', fn ($q) => $q->where('job_family', $request->string('job_family')->toString()))
                    ->when($request->string('grade_level')->toString() !== '', fn ($q) => $q->where('grade_level', $request->string('grade_level')->toString()))
                    ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
                    ->orderBy('job_position_code')
                    ->get();
            }
        }

        return Inertia::render('Positions/Index', [
            'organizationTree' => $organizationTree,
            'hasPublishedHierarchy' => $hasPublishedHierarchy,
            'selectedOrganization' => $selectedOrganization,
            'organizationUnits' => $organizationUnits,
            'selectedUnit' => $selectedUnit,
            'positions' => PositionResource::collection($positions)->resolve(),
            'filters' => $request->only(['search', 'job_family', 'grade_level', 'is_active']),
            'can' => [
                'create' => $user?->can('create', Position::class) ?? false,
            ],
        ]);
    }

    public function create(Request $request, OrganizationScopeService $organizationScopeService): Response
    {
        $this->authorize('create', Position::class);

        $organizationId = $request->string('organization_id')->toString() ?: null;
        $organizationUnitId = $request->string('organization_unit_id')->toString() ?: null;

        $organizationUnits = $organizationId
            ? OrganizationUnit::query()
                ->where('organization_id', $organizationId)
                ->whereNull('deleted_at')
                ->orderBy('name_en')
                ->get(['id', 'name_en', 'code', 'organization_unit_type_id'])
                ->toArray()
            : [];

        $occupations = Occupation::query()
            ->whereNull('deleted_at')
            ->orderBy('isco_code')
            ->get(['id', 'isco_code', 'name_en', 'name_am'])
            ->toArray();

        $gradeLevels = GradeLevel::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        return Inertia::render('Positions/Create', [
            'organizations' => Organization::query()
                ->whereIn('id', $organizationScopeService->accessibleOrganizationIds(request()->user()))
                ->orderBy('name_en')
                ->get(['id', 'name_en']),
            'organizationUnits' => $organizationUnits,
            'occupations' => $occupations,
            'gradeLevels' => $gradeLevels,
            'selectedOrganizationId' => $organizationId,
            'selectedOrganizationUnitId' => $organizationUnitId,
        ]);
    }

    public function store(StorePositionRequest $request, CreatePositionAction $action): RedirectResponse
    {
        $position = $action->execute($request->validated(), $request->user());

        return to_route('positions.show', $position)
            ->with('flash', ['message' => __('Position created successfully.'), 'type' => 'success']);
    }

    public function show(Position $position): Response
    {
        $this->authorize('view', $position);

        $position->load('organization:id,name_en');
        $position->loadCount('assignments');

        return Inertia::render('Positions/Show', [
            'position' => (new PositionResource($position))->resolve(),
        ]);
    }

    public function edit(Position $position, OrganizationScopeService $organizationScopeService): Response
    {
        $this->authorize('update', $position);

        $position->load('organization:id,name_en', 'organizationUnit:id,name_en,code', 'occupation:id,isco_code,name_en,name_am');

        $organizationUnits = $position->organization_id
            ? OrganizationUnit::query()
                ->where('organization_id', $position->organization_id)
                ->whereNull('deleted_at')
                ->orderBy('name_en')
                ->get(['id', 'name_en', 'code', 'organization_unit_type_id'])
                ->toArray()
            : [];

        $occupations = Occupation::query()
            ->whereNull('deleted_at')
            ->orderBy('isco_code')
            ->get(['id', 'isco_code', 'name_en', 'name_am'])
            ->toArray();

        $gradeLevels = GradeLevel::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        return Inertia::render('Positions/Edit', [
            'position' => (new PositionResource($position))->resolve(),
            'organizations' => Organization::query()
                ->whereIn('id', $organizationScopeService->accessibleOrganizationIds(request()->user()))
                ->orderBy('name_en')
                ->get(['id', 'name_en']),
            'organizationUnits' => $organizationUnits,
            'occupations' => $occupations,
            'gradeLevels' => $gradeLevels,
        ]);
    }

    public function update(UpdatePositionRequest $request, Position $position, UpdatePositionAction $action): RedirectResponse
    {
        $action->execute($position, $request->validated(), $request->user());

        return to_route('positions.show', $position)
            ->with('flash', ['message' => __('Position updated successfully.'), 'type' => 'success']);
    }

    public function archive(Request $request, Position $position, ArchivePositionAction $action): RedirectResponse
    {
        $this->authorize('archive', $position);

        $action->execute($position, $request->user(), $request->string('reason')->toString() ?: null, $request);

        return to_route('positions.index')->with('flash', ['message' => __('recycle-bin.deleted_successfully'), 'type' => 'success']);
    }

    public function restore(Request $request, string $position, RestorePositionAction $action): RedirectResponse
    {
        $position = Position::query()->withTrashed()->findOrFail($position);

        $this->authorize('restore', $position);

        $action->execute($position, $request->user(), $request);

        return back()->with('flash', ['message' => __('recycle-bin.restored_successfully'), 'type' => 'success']);
    }
}
