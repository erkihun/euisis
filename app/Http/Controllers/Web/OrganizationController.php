<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Organizations\DeleteOrganizationAction;
use App\Actions\Organizations\CreateOrganizationAction;
use App\Actions\Organizations\UpdateOrganizationAction;
use App\Enums\HierarchyVersionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationStoreRequest;
use App\Http\Requests\OrganizationUpdateRequest;
use App\Http\Resources\ReportingLineResource;
use App\Models\HierarchyVersion;
use App\Models\InstitutionOffice;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Services\OrganizationRelationships\ReportingLineService;
use App\Services\Organizations\ParentOrganizationOptionsService;
use App\Services\OrganizationScope\OrganizationScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function index(OrganizationScopeService $scopeService): Response
    {
        $user = Auth::user();

        $publishedVersion = HierarchyVersion::query()
            ->where('status', 'published')
            ->latest('approval_date')
            ->first();

        $allowedOrgIds = $user !== null ? $scopeService->accessibleOrganizationIds($user) : collect();

        $tree = $scopeService->buildFlatTreeForIndex($publishedVersion, $allowedOrgIds->isNotEmpty() ? $allowedOrgIds->all() : null);

        if ($user !== null) {
            $organizationsById = Organization::query()
                ->whereIn('id', collect($tree)->pluck('id'))
                ->get()
                ->keyBy('id');

            $tree = collect($tree)
                ->map(function (array $node) use ($organizationsById, $user): array {
                    $organization = $organizationsById->get($node['id']);

                    $node['can'] = [
                        'createChild' => $organization !== null
                            ? $user->can('createChild', $organization)
                            : false,
                    ];

                    return $node;
                })
                ->all();
        }

        $assignedIds = collect($tree)->pluck('id');

        $unassignedQuery = Organization::query()
            ->when($assignedIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $assignedIds))
            ->with('type:id,name_en,name_am,code')
            ->orderBy('name_en');

        if ($allowedOrgIds->isNotEmpty()) {
            $unassignedQuery->whereIn('id', $allowedOrgIds);
        }

        $unassigned = $unassignedQuery->get(['id', 'code', 'name_en', 'name_am', 'status', 'effective_from', 'effective_to', 'organization_type_id']);

        $canManage = $user?->can('organizations.manage') ?? false;

        return Inertia::render('Organizations/Index', [
            'tree' => $tree,
            'unassigned' => $unassigned,
            'publishedVersion' => $publishedVersion?->only(['id', 'version_name', 'approval_date']),
            'hierarchyVersions' => HierarchyVersion::query()->orderByDesc('created_at')->get(['id', 'version_name', 'status', 'approval_date']),
            'can' => [
                'create' => $canManage,
                'manageHierarchy' => $canManage,
            ],
        ]);
    }

    public function create(Request $request, ParentOrganizationOptionsService $parentOrganizationOptionsService): Response
    {
        $this->authorize('create', Organization::class);

        $selectedParentId = $request->string('parent')->toString() ?: null;
        $parentOptions = $parentOrganizationOptionsService->resolve(
            $request->user(),
            selectedId: $selectedParentId,
        );

        return Inertia::render('Organizations/Create', [
            'organizationTypes' => OrganizationType::query()->orderBy('name_en')->get(['id', 'name_en', 'name_am', 'code']),
            'hierarchyVersions' => HierarchyVersion::query()
                ->where('status', HierarchyVersionStatus::Draft->value)
                ->orderByDesc('created_at')
                ->get(['id', 'version_name', 'status']),
            'parentOrganizationOptions' => $parentOptions['options'],
            'selectedParentOrganization' => $parentOptions['selected'],
        ]);
    }

    public function parentOptions(
        Request $request,
        ParentOrganizationOptionsService $parentOrganizationOptionsService,
    ): JsonResponse {
        $this->authorize('create', Organization::class);

        $resolved = $parentOrganizationOptionsService->resolve(
            $request->user(),
            search: $request->string('q')->toString() ?: null,
            selectedId: $request->string('selected_id')->toString() ?: null,
            hierarchyVersionId: $request->string('hierarchy_version_id')->toString() ?: null,
            currentOrganizationId: $request->string('current_organization_id')->toString() ?: null,
        );

        return response()->json($resolved);
    }

    public function show(
        Organization $organization,
        OrganizationScopeService $organizationScopeService,
        ReportingLineService $reportingLineService,
    ): Response {
        $user = Auth::user();

        $latestVersionId = HierarchyVersion::query()
            ->where('status', 'published')
            ->latest('approval_date')
            ->value('id');

        $organization->load(['type', 'mergedInto', 'nameHistories']);

        $parentOrganizationId = $latestVersionId !== null
            ? $organization->parentEdges()
                ->where('hierarchy_version_id', $latestVersionId)
                ->value('parent_organization_id')
            : null;

        $institutionOffices = InstitutionOffice::query()
            ->where('institution_id', $organization->id)
            ->orderBy('name_en')
            ->get(['id', 'office_code', 'name_en', 'office_level', 'status'])
            ->toArray();

        return Inertia::render('Organizations/Show', [
            'organization' => $organization,
            'parentOrganizationId' => $parentOrganizationId,
            'currentAssignmentsCount' => $organization->assignments()->where('is_current', true)->count(),
            'descendants' => $latestVersionId === null
                ? []
                : $organizationScopeService->descendantsForOrganization($organization->id, $latestVersionId),
            'institutionOffices' => $institutionOffices,
            'reportingOffices' => ReportingLineResource::collection(
                $reportingLineService->getOfficesReportingToOrganization($organization),
            )->resolve(request()),
            'reportingUnits' => ReportingLineResource::collection(
                $reportingLineService->getUnitsReportingToOrganization($organization),
            )->resolve(request()),
            'can' => [
                'update' => $user?->can('update', $organization) ?? false,
                'delete' => $user?->can('delete', $organization) ?? false,
                'createChild' => $user?->can('createChild', $organization) ?? false,
            ],
        ]);
    }

    public function store(OrganizationStoreRequest $request, CreateOrganizationAction $createOrganizationAction): RedirectResponse
    {
        $parentOrganizationId = $request->validated('parent_organization_id');
        $organization = $createOrganizationAction->execute($request->validated(), $request->user());

        if ($parentOrganizationId !== null) {
            return to_route('organizations.show', $parentOrganizationId)
                ->with('flash', ['message' => __('organizations.child_organization_created_successfully'), 'type' => 'success']);
        }

        return to_route('organizations.show', $organization)
            ->with('flash', ['message' => __('organizations.created_successfully'), 'type' => 'success']);
    }

    public function edit(Organization $organization): Response
    {
        $this->authorize('update', $organization);

        return Inertia::render('Organizations/Edit', [
            'organization' => $organization->load('type'),
            'organizationTypes' => OrganizationType::query()->orderBy('name_en')->get(['id', 'name_en', 'name_am', 'code']),
        ]);
    }

    public function update(
        OrganizationUpdateRequest $request,
        Organization $organization,
        UpdateOrganizationAction $updateOrganizationAction,
    ): RedirectResponse {
        $updateOrganizationAction->execute($request->validated(), $organization, $request->user());

        return to_route('organizations.show', $organization);
    }

    public function archive(
        Request $request,
        Organization $organization,
        DeleteOrganizationAction $deleteOrganizationAction,
    ): RedirectResponse {
        $this->authorize('delete', $organization);

        $deleteOrganizationAction->execute($organization, $request->user());

        return to_route('organizations.index');
    }
}
