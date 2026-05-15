<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\OrganizationUnits\ArchiveOrganizationUnitAction;
use App\Actions\OrganizationUnits\CreateOrganizationUnitAction;
use App\Actions\OrganizationUnits\RestoreOrganizationUnitAction;
use App\Actions\OrganizationUnits\UpdateOrganizationUnitAction;
use App\Enums\OrganizationUnitStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizationUnitRequest;
use App\Http\Requests\UpdateOrganizationUnitRequest;
use App\Http\Resources\OrganizationUnitResource;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\OrganizationUnitType as OrganizationUnitTypeModel;
use App\Services\OrganizationUnits\OrganizationUnitTreeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationUnitController extends Controller
{
    public function index(Request $request, OrganizationUnitTreeService $treeService): Response
    {
        $this->authorize('viewAny', OrganizationUnit::class);

        $user = Auth::user();
        $accessibleOrgIds = $user?->accessibleOrganizationIds() ?? [];

        $orgQuery = Organization::query()
            ->with(['type:id,name_en,name_am,code'])
            ->withCount(['organizationUnits' => fn ($q) => $q->whereNull('deleted_at')])
            ->orderBy('name_en');

        if (! empty($accessibleOrgIds)) {
            $orgQuery->whereIn('id', $accessibleOrgIds);
        }

        $organizations = $orgQuery->get([
            'id', 'organization_type_id', 'code', 'name_en', 'name_am',
            'status', 'logo_path', 'effective_from',
        ]);

        $selectedOrganization = null;
        $organizationUnits = [];

        if ($request->filled('organization_id')) {
            $selectedOrganization = Organization::query()
                ->with(['type:id,name_en,name_am,code'])
                ->withCount(['organizationUnits' => fn ($q) => $q->whereNull('deleted_at')])
                ->find($request->get('organization_id'), [
                    'id', 'organization_type_id', 'code', 'name_en', 'name_am',
                    'status', 'logo_path', 'effective_from',
                ]);

            if ($selectedOrganization !== null) {
                $organizationUnits = $treeService->buildTreeWithMeta($selectedOrganization->id, $user);
            }
        }

        return Inertia::render('OrganizationUnits/Index', [
            'organizations' => $organizations,
            'selectedOrganization' => $selectedOrganization,
            'organizationUnits' => $organizationUnits,
            'unitTypes' => OrganizationUnitTypeModel::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name_en')
                ->get(['id', 'code', 'name_en', 'name_am']),
            'can' => [
                'viewAny' => $user?->can('viewAny', OrganizationUnit::class) ?? false,
                'create' => $user?->can('create', OrganizationUnit::class) ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', OrganizationUnit::class);

        $user = Auth::user();
        $accessibleOrgIds = $user?->accessibleOrganizationIds() ?? [];

        $orgQuery = Organization::query()->orderBy('name_en');

        if (! empty($accessibleOrgIds)) {
            $orgQuery->whereIn('id', $accessibleOrgIds);
        }

        $selectedOrg = null;
        $parentUnits = [];

        if ($request->filled('organization_id')) {
            $selectedOrg = Organization::query()->find(
                $request->get('organization_id'),
                ['id', 'code', 'name_en', 'name_am'],
            );
        }

        if ($selectedOrg !== null) {
            $treeService = app(OrganizationUnitTreeService::class);
            $parentUnits = $treeService->optionsForOrganization($selectedOrg->id);
        }

        return Inertia::render('OrganizationUnits/Create', [
            'organizations' => $orgQuery->get(['id', 'name_en', 'name_am', 'code']),
            'selectedOrg' => $selectedOrg,
            'parentUnits' => $parentUnits,
            'unitTypes' => OrganizationUnitTypeModel::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name_en')
                ->get(['id', 'code', 'name_en', 'name_am']),
            'statusOptions' => array_map(
                fn ($c) => ['value' => $c->value, 'label' => ucfirst($c->value)],
                OrganizationUnitStatus::cases(),
            ),
        ]);
    }

    public function store(StoreOrganizationUnitRequest $request, CreateOrganizationUnitAction $action): RedirectResponse
    {
        $unit = $action->execute($request->validated(), $request->user());

        return redirect()->route('organization-units.show', $unit)
            ->with('success', __('organization-units.created_successfully'));
    }

    public function show(OrganizationUnit $organizationUnit): Response
    {
        $this->authorize('view', $organizationUnit);

        $organizationUnit->load([
            'organization:id,name_en,name_am,code',
            'parent:id,name_en,code',
            'children',
        ]);

        $organizationUnit->loadCount('children');

        return Inertia::render('OrganizationUnits/Show', [
            'unit' => (new OrganizationUnitResource($organizationUnit))->resolve(request()),
        ]);
    }

    public function edit(OrganizationUnit $organizationUnit, OrganizationUnitTreeService $treeService): Response
    {
        $this->authorize('update', $organizationUnit);

        $organizationUnit->load(['organization:id,name_en,name_am,code', 'parent:id,name_en,code']);

        return Inertia::render('OrganizationUnits/Edit', [
            'unit' => (new OrganizationUnitResource($organizationUnit))->resolve(request()),
            'unitTypes' => OrganizationUnitTypeModel::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name_en')
                ->get(['id', 'code', 'name_en', 'name_am']),
            'statusOptions' => array_map(
                fn ($c) => ['value' => $c->value, 'label' => ucfirst($c->value)],
                OrganizationUnitStatus::cases(),
            ),
            'parentOptions' => $treeService->optionsForOrganization($organizationUnit->organization_id),
        ]);
    }

    public function update(UpdateOrganizationUnitRequest $request, OrganizationUnit $organizationUnit, UpdateOrganizationUnitAction $action): RedirectResponse
    {
        $unit = $action->execute($organizationUnit, $request->validated(), $request->user());

        return redirect()->route('organization-units.show', $unit)
            ->with('success', __('organization-units.updated_successfully'));
    }

    public function archive(Request $request, OrganizationUnit $organizationUnit, ArchiveOrganizationUnitAction $action): RedirectResponse
    {
        $this->authorize('archive', $organizationUnit);

        $action->execute($organizationUnit, $request->user(), $request->string('reason')->toString() ?: null, $request);

        return redirect()->route('organization-units.index')
            ->with('success', __('recycle-bin.deleted_successfully'));
    }

    public function restore(Request $request, string $organizationUnit, RestoreOrganizationUnitAction $action): RedirectResponse
    {
        /** @var OrganizationUnit|null $unit */
        $unit = OrganizationUnit::withTrashed()->findOrFail($organizationUnit);

        $this->authorize('restore', $unit);

        $action->execute($unit, $request->user(), $request);

        return redirect()->route('organization-units.show', $unit)
            ->with('success', __('recycle-bin.restored_successfully'));
    }

    public function options(Organization $organization, OrganizationUnitTreeService $treeService): JsonResponse
    {
        return response()->json($treeService->optionsForOrganization($organization->id));
    }

    public function tree(Organization $organization, OrganizationUnitTreeService $treeService): JsonResponse
    {
        $this->authorize('viewAny', OrganizationUnit::class);

        return response()->json($treeService->buildTree($organization->id));
    }
}
