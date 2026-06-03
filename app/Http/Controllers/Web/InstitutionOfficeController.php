<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\InstitutionOffices\CreateInstitutionOfficeAction;
use App\Actions\InstitutionOffices\DeleteInstitutionOfficeAction;
use App\Actions\InstitutionOffices\MoveInstitutionOfficeAction;
use App\Actions\InstitutionOffices\RestoreInstitutionOfficeAction;
use App\Actions\InstitutionOffices\UpdateInstitutionOfficeAction;
use App\Enums\InstitutionOfficeLevel;
use App\Enums\InstitutionOfficeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\InstitutionOffices\StoreInstitutionOfficeRequest;
use App\Http\Requests\InstitutionOffices\UpdateInstitutionOfficeRequest;
use App\Http\Resources\InstitutionOfficeResource;
use App\Models\InstitutionOffice;
use App\Models\Organization;
use App\Services\InstitutionOffices\InstitutionOfficeTreeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class InstitutionOfficeController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', InstitutionOffice::class);

        $query = InstitutionOffice::query()
            ->with(['institution:id,name_en,code', 'geographicOrganization:id,name_en,code', 'parentOffice:id,name_en,office_code'])
            ->withCount('childOffices');

        if ($request->filled('institution_id')) {
            $query->where('institution_id', $request->string('institution_id')->toString());
        }

        if ($request->filled('office_level')) {
            $query->where('office_level', $request->string('office_level')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search): void {
                $q->where('name_en', 'like', "%{$search}%")
                    ->orWhere('name_am', 'like', "%{$search}%")
                    ->orWhere('office_code', 'like', "%{$search}%");
            });
        }

        $offices = $query->orderBy('name_en')->paginate(25)->withQueryString();

        $user = Auth::user();

        return Inertia::render('InstitutionOffices/Index', [
            'offices' => InstitutionOfficeResource::collection($offices),
            'institutions' => Organization::query()->orderBy('name_en')->get(['id', 'name_en', 'name_am', 'code']),
            'levelOptions' => array_map(
                fn (InstitutionOfficeLevel $c) => ['value' => $c->value, 'label' => $c->label()],
                InstitutionOfficeLevel::cases(),
            ),
            'statusOptions' => array_map(
                fn (InstitutionOfficeStatus $c) => ['value' => $c->value, 'label' => ucfirst($c->value)],
                InstitutionOfficeStatus::cases(),
            ),
            'filters' => $request->only(['institution_id', 'office_level', 'status', 'search']),
            'can' => [
                'create' => $user?->can('create', InstitutionOffice::class) ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', InstitutionOffice::class);

        $institutionId = $request->string('institution_id')->toString() ?: null;

        $selectedInstitution = $institutionId !== null
            ? Organization::query()->find($institutionId, ['id', 'name_en', 'name_am', 'code'])
            : null;

        $parentOfficeOptions = [];

        if ($selectedInstitution !== null) {
            $parentOfficeOptions = InstitutionOffice::query()
                ->forInstitution($selectedInstitution->id)
                ->orderBy('name_en')
                ->get(['id', 'name_en', 'name_am', 'office_code', 'office_level'])
                ->toArray();
        }

        return Inertia::render('InstitutionOffices/Create', [
            'institutions' => Organization::query()->orderBy('name_en')->get(['id', 'name_en', 'name_am', 'code']),
            'selectedInstitution' => $selectedInstitution,
            'parentOfficeOptions' => $parentOfficeOptions,
            'geographicOrgs' => Organization::query()->orderBy('name_en')->get(['id', 'name_en', 'name_am', 'code']),
            'levelOptions' => array_map(
                fn (InstitutionOfficeLevel $c) => ['value' => $c->value, 'label' => $c->label()],
                InstitutionOfficeLevel::cases(),
            ),
            'statusOptions' => array_map(
                fn (InstitutionOfficeStatus $c) => ['value' => $c->value, 'label' => ucfirst($c->value)],
                InstitutionOfficeStatus::cases(),
            ),
        ]);
    }

    public function store(
        StoreInstitutionOfficeRequest $request,
        CreateInstitutionOfficeAction $action,
    ): RedirectResponse {
        $office = $action->execute($request->validated(), $request->user(), $request);

        return to_route('institution-offices.show', $office)
            ->with('flash', ['message' => __('institution-offices.messages.created'), 'type' => 'success']);
    }

    public function show(InstitutionOffice $institutionOffice): Response
    {
        $this->authorize('view', $institutionOffice);

        $institutionOffice->load([
            'institution:id,name_en,name_am,code',
            'geographicOrganization:id,name_en,name_am,code',
            'parentOffice:id,name_en,office_code',
            'childOffices',
        ]);

        $institutionOffice->loadCount('childOffices');

        $user = Auth::user();

        return Inertia::render('InstitutionOffices/Show', [
            'office' => (new InstitutionOfficeResource($institutionOffice))->resolve(request()),
            'can' => [
                'update' => $user?->can('update', $institutionOffice) ?? false,
                'delete' => $user?->can('delete', $institutionOffice) ?? false,
                'restore' => $user?->can('restore', $institutionOffice) ?? false,
                'move' => $user?->can('move', $institutionOffice) ?? false,
                'create' => $user?->can('create', InstitutionOffice::class) ?? false,
            ],
        ]);
    }

    public function edit(InstitutionOffice $institutionOffice): Response
    {
        $this->authorize('update', $institutionOffice);

        $institutionOffice->load([
            'institution:id,name_en,name_am,code',
            'geographicOrganization:id,name_en,name_am,code',
            'parentOffice:id,name_en,office_code',
        ]);

        $parentOfficeOptions = InstitutionOffice::query()
            ->forInstitution($institutionOffice->institution_id)
            ->where('id', '!=', $institutionOffice->id)
            ->orderBy('name_en')
            ->get(['id', 'name_en', 'name_am', 'office_code', 'office_level'])
            ->toArray();

        return Inertia::render('InstitutionOffices/Edit', [
            'office' => (new InstitutionOfficeResource($institutionOffice))->resolve(request()),
            'institutions' => Organization::query()->orderBy('name_en')->get(['id', 'name_en', 'name_am', 'code']),
            'parentOfficeOptions' => $parentOfficeOptions,
            'geographicOrgs' => Organization::query()->orderBy('name_en')->get(['id', 'name_en', 'name_am', 'code']),
            'levelOptions' => array_map(
                fn (InstitutionOfficeLevel $c) => ['value' => $c->value, 'label' => $c->label()],
                InstitutionOfficeLevel::cases(),
            ),
            'statusOptions' => array_map(
                fn (InstitutionOfficeStatus $c) => ['value' => $c->value, 'label' => ucfirst($c->value)],
                InstitutionOfficeStatus::cases(),
            ),
        ]);
    }

    public function update(
        UpdateInstitutionOfficeRequest $request,
        InstitutionOffice $institutionOffice,
        UpdateInstitutionOfficeAction $action,
    ): RedirectResponse {
        $action->execute($institutionOffice, $request->validated(), $request->user(), $request);

        return to_route('institution-offices.show', $institutionOffice)
            ->with('flash', ['message' => __('institution-offices.messages.updated'), 'type' => 'success']);
    }

    public function destroy(
        Request $request,
        InstitutionOffice $institutionOffice,
        DeleteInstitutionOfficeAction $action,
    ): RedirectResponse {
        $this->authorize('delete', $institutionOffice);

        $action->execute($institutionOffice, $request->user(), $request);

        return to_route('institution-offices.index')
            ->with('flash', ['message' => __('institution-offices.messages.deleted'), 'type' => 'success']);
    }

    public function restore(
        Request $request,
        string $institutionOffice,
        RestoreInstitutionOfficeAction $action,
    ): RedirectResponse {
        /** @var InstitutionOffice|null $office */
        $office = InstitutionOffice::withTrashed()->findOrFail($institutionOffice);

        $this->authorize('restore', $office);

        $action->execute($office, $request->user(), $request);

        return to_route('institution-offices.show', $office)
            ->with('flash', ['message' => __('institution-offices.messages.restored'), 'type' => 'success']);
    }

    public function move(
        Request $request,
        InstitutionOffice $institutionOffice,
        MoveInstitutionOfficeAction $action,
        InstitutionOfficeTreeService $treeService,
    ): RedirectResponse {
        $this->authorize('move', $institutionOffice);

        $newParentId = $request->string('parent_office_id')->toString() ?: null;

        // Validate the new parent is not a descendant of this office
        if ($newParentId !== null) {
            $newParent = InstitutionOffice::query()->findOrFail($newParentId);

            if (! $treeService->canBeParent($newParent, $institutionOffice)) {
                return back()->withErrors([
                    'parent_office_id' => __('institution-offices.validation.circular_hierarchy_not_allowed'),
                ]);
            }
        }

        $action->execute($institutionOffice, $newParentId, $request->user(), $request);

        return to_route('institution-offices.show', $institutionOffice)
            ->with('flash', ['message' => __('institution-offices.messages.moved'), 'type' => 'success']);
    }

    public function tree(Organization $organization, InstitutionOfficeTreeService $treeService): JsonResponse
    {
        $this->authorize('viewAny', InstitutionOffice::class);

        $tree = $treeService->getTreeForInstitution($organization);

        return response()->json($tree);
    }

    public function parentOptions(Request $request, Organization $organization): JsonResponse
    {
        $this->authorize('viewAny', InstitutionOffice::class);

        $query = InstitutionOffice::query()
            ->forInstitution($organization->id)
            ->orderBy('name_en');

        if ($request->filled('exclude')) {
            $query->where('id', '!=', $request->string('exclude')->toString());
        }

        return response()->json(
            $query->get(['id', 'name_en', 'name_am', 'office_code', 'office_level'])->toArray(),
        );
    }
}
