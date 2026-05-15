<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Positions\ArchivePositionAction;
use App\Actions\Positions\CreatePositionAction;
use App\Actions\Positions\RestorePositionAction;
use App\Actions\Positions\UpdatePositionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Http\Resources\PositionResource;
use App\Models\Organization;
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

        $accessibleOrganizationIds = $organizationScopeService->accessibleOrganizationIds($request->user());

        $positions = Position::query()
            ->with('organization:id,name_en')
            ->withCount('assignments')
            ->when(
                ! $request->user()->hasRole(['Super Admin', 'City Admin']),
                fn ($query) => $query->where(function ($inner) use ($accessibleOrganizationIds): void {
                    $inner->whereNull('organization_id')
                        ->orWhereIn('organization_id', $accessibleOrganizationIds);
                })
            )
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($nested) use ($search): void {
                    $nested->where('job_position_code', 'like', "%{$search}%")
                        ->orWhere('title_en', 'like', "%{$search}%")
                        ->orWhere('title_am', 'like', "%{$search}%");
                });
            })
            ->when($request->string('organization_id')->toString() !== '', fn ($query) => $query->where('organization_id', $request->string('organization_id')->toString()))
            ->when($request->string('job_family')->toString() !== '', fn ($query) => $query->where('job_family', $request->string('job_family')->toString()))
            ->when($request->string('grade_level')->toString() !== '', fn ($query) => $query->where('grade_level', $request->string('grade_level')->toString()))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('job_position_code')
            ->get();

        return Inertia::render('Positions/Index', [
            'positions' => PositionResource::collection($positions)->resolve(),
            'filters' => $request->only(['search', 'organization_id', 'job_family', 'grade_level', 'is_active']),
            'organizations' => Organization::query()
                ->whereIn('id', $accessibleOrganizationIds)
                ->orderBy('name_en')
                ->get(['id', 'name_en']),
            'can' => [
                'create' => $request->user()?->can('create', Position::class) ?? false,
            ],
        ]);
    }

    public function create(OrganizationScopeService $organizationScopeService): Response
    {
        $this->authorize('create', Position::class);

        return Inertia::render('Positions/Create', [
            'organizations' => Organization::query()
                ->whereIn('id', $organizationScopeService->accessibleOrganizationIds(request()->user()))
                ->orderBy('name_en')
                ->get(['id', 'name_en']),
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

        $position->load('organization:id,name_en');

        return Inertia::render('Positions/Edit', [
            'position' => (new PositionResource($position))->resolve(),
            'organizations' => Organization::query()
                ->whereIn('id', $organizationScopeService->accessibleOrganizationIds(request()->user()))
                ->orderBy('name_en')
                ->get(['id', 'name_en']),
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
