<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Occupations\ArchiveOccupationAction;
use App\Actions\Occupations\CreateOccupationAction;
use App\Actions\Occupations\RestoreOccupationAction;
use App\Actions\Occupations\UpdateOccupationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOccupationRequest;
use App\Http\Requests\UpdateOccupationRequest;
use App\Http\Resources\OccupationResource;
use App\Models\Occupation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OccupationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Occupation::class);

        $query = Occupation::query()
            ->when($request->string('search')->toString() !== '', function ($q) use ($request): void {
                $search = $request->string('search')->toString();
                $q->where(function ($nested) use ($search): void {
                    $nested->where('isco_code', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%")
                        ->orWhere('name_am', 'like', "%{$search}%")
                        ->orWhere('skill_specialization', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('isco_major_group_code'), fn ($q) => $q->where('isco_major_group_code', $request->string('isco_major_group_code')->toString()))
            ->when($request->filled('skill_level'), fn ($q) => $q->where('skill_level', $request->string('skill_level')->toString()))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('sort_order')
            ->orderBy('isco_code');

        $occupations = $query->paginate(30)->withQueryString();

        $majorGroups = Occupation::query()
            ->whereNotNull('isco_major_group_code')
            ->distinct()
            ->orderBy('isco_major_group_code')
            ->pluck('isco_major_group_code')
            ->values()
            ->all();

        $skillLevels = Occupation::query()
            ->whereNotNull('skill_level')
            ->distinct()
            ->orderBy('skill_level')
            ->pluck('skill_level')
            ->values()
            ->all();

        return Inertia::render('Occupations/Index', [
            'occupations' => OccupationResource::collection($occupations)->resolve(),
            'meta' => [
                'current_page' => $occupations->currentPage(),
                'last_page' => $occupations->lastPage(),
                'total' => $occupations->total(),
                'per_page' => $occupations->perPage(),
            ],
            'filters' => $request->only(['search', 'isco_major_group_code', 'skill_level', 'is_active']),
            'majorGroups' => $majorGroups,
            'skillLevels' => $skillLevels,
            'can' => [
                'create' => $request->user()?->can('create', Occupation::class) ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Occupation::class);

        return Inertia::render('Occupations/Create');
    }

    public function store(StoreOccupationRequest $request, CreateOccupationAction $action): RedirectResponse
    {
        $occupation = $action->execute($request->validated(), $request->user());

        return to_route('occupations.show', $occupation)
            ->with('flash', ['message' => __('occupations.created_successfully'), 'type' => 'success']);
    }

    public function show(Occupation $occupation): Response
    {
        $this->authorize('view', $occupation);

        return Inertia::render('Occupations/Show', [
            'occupation' => (new OccupationResource($occupation))->resolve(),
        ]);
    }

    public function edit(Occupation $occupation): Response
    {
        $this->authorize('update', $occupation);

        return Inertia::render('Occupations/Edit', [
            'occupation' => (new OccupationResource($occupation))->resolve(),
        ]);
    }

    public function update(UpdateOccupationRequest $request, Occupation $occupation, UpdateOccupationAction $action): RedirectResponse
    {
        $action->execute($occupation, $request->validated(), $request->user());

        return to_route('occupations.show', $occupation)
            ->with('flash', ['message' => __('occupations.updated_successfully'), 'type' => 'success']);
    }

    public function archive(Request $request, Occupation $occupation, ArchiveOccupationAction $action): RedirectResponse
    {
        $this->authorize('archive', $occupation);

        $action->execute($occupation, $request->user(), $request->string('reason')->toString() ?: null, $request);

        return to_route('occupations.index')
            ->with('flash', ['message' => __('occupations.archived_successfully'), 'type' => 'success']);
    }

    public function restore(Request $request, string $occupation, RestoreOccupationAction $action): RedirectResponse
    {
        $occupation = Occupation::query()->withTrashed()->findOrFail($occupation);

        $this->authorize('restore', $occupation);

        $action->execute($occupation, $request->user(), $request);

        return back()->with('flash', ['message' => __('occupations.restored_successfully'), 'type' => 'success']);
    }
}
