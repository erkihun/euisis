<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Cafeteria\ArchiveCafeteriaProviderAction;
use App\Actions\Cafeteria\CreateCafeteriaProviderAction;
use App\Actions\Cafeteria\RestoreCafeteriaProviderAction;
use App\Actions\Cafeteria\UpdateCafeteriaProviderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCafeteriaProviderRequest;
use App\Http\Requests\UpdateCafeteriaProviderRequest;
use App\Http\Resources\CafeteriaProviderResource;
use App\Models\CafeteriaProvider;
use App\Models\Organization;
use App\Services\OrganizationScope\OrganizationScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CafeteriaProviderController extends Controller
{
    public function __construct(private readonly OrganizationScopeService $orgScope) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CafeteriaProvider::class);

        $isActiveFilter = $request->string('is_active')->toString();

        $query = CafeteriaProvider::query()
            ->when($isActiveFilter === '0', fn ($q) => $q->onlyTrashed())
            ->when($isActiveFilter === '', fn ($q) => $q->withoutTrashed())
            ->when($request->string('search')->toString() !== '', function ($q) use ($request): void {
                $search = $request->string('search')->toString();
                $q->where(function ($nested) use ($search): void {
                    $nested->where('code', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%")
                        ->orWhere('name_am', 'like', "%{$search}%");
                });
            })
            ->orderBy('name_en');

        $providers = $query->paginate(30)->withQueryString();

        return Inertia::render('Cafeteria/Providers/Index', [
            'providers' => CafeteriaProviderResource::collection($providers)->resolve(),
            'meta'      => [
                'current_page' => $providers->currentPage(),
                'last_page'    => $providers->lastPage(),
                'total'        => $providers->total(),
                'per_page'     => $providers->perPage(),
            ],
            'filters' => $request->only(['search', 'is_active']),
            'can'     => [
                'create' => $request->user()?->can('create', CafeteriaProvider::class) ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', CafeteriaProvider::class);

        return Inertia::render('Cafeteria/Providers/Create', [
            'organizations' => $this->organizationOptions($request),
        ]);
    }

    public function store(StoreCafeteriaProviderRequest $request, CreateCafeteriaProviderAction $action): RedirectResponse
    {
        $provider = $action->execute($request->validated(), $request->user(), $request);

        return to_route('cafeteria.providers.show', $provider)
            ->with('flash', ['message' => __('cafeteria.providerCreated'), 'type' => 'success']);
    }

    public function show(CafeteriaProvider $cafeteriaProvider): Response
    {
        $this->authorize('view', $cafeteriaProvider);

        return Inertia::render('Cafeteria/Providers/Show', [
            'provider' => (new CafeteriaProviderResource($cafeteriaProvider))->resolve(),
        ]);
    }

    public function edit(Request $request, CafeteriaProvider $cafeteriaProvider): Response
    {
        $this->authorize('update', $cafeteriaProvider);

        $cafeteriaProvider->load('organization:id,name_en,name_am,code');

        return Inertia::render('Cafeteria/Providers/Edit', [
            'provider'      => (new CafeteriaProviderResource($cafeteriaProvider))->resolve(),
            'organizations' => $this->organizationOptions($request),
        ]);
    }

    public function update(UpdateCafeteriaProviderRequest $request, CafeteriaProvider $cafeteriaProvider, UpdateCafeteriaProviderAction $action): RedirectResponse
    {
        $action->execute($cafeteriaProvider, $request->validated(), $request->user(), $request);

        return to_route('cafeteria.providers.show', $cafeteriaProvider)
            ->with('flash', ['message' => __('cafeteria.providerUpdated'), 'type' => 'success']);
    }

    public function archive(Request $request, CafeteriaProvider $cafeteriaProvider, ArchiveCafeteriaProviderAction $action): RedirectResponse
    {
        $this->authorize('archive', $cafeteriaProvider);

        $action->execute($cafeteriaProvider, $request->user(), $request->string('reason')->toString() ?: null, $request);

        return to_route('cafeteria.providers.index')
            ->with('flash', ['message' => __('cafeteria.providerArchived'), 'type' => 'success']);
    }

    public function restore(Request $request, string $cafeteriaProvider, RestoreCafeteriaProviderAction $action): RedirectResponse
    {
        $provider = CafeteriaProvider::query()->withTrashed()->findOrFail($cafeteriaProvider);

        $this->authorize('restore', $provider);

        $action->execute($provider, $request->user(), $request);

        return back()->with('flash', ['message' => __('cafeteria.providerRestored'), 'type' => 'success']);
    }

    /** @return array<int, array<string, string|null>> */
    private function organizationOptions(Request $request): array
    {
        $user  = $request->user();
        $query = Organization::query()
            ->where('status', \App\Enums\OrganizationStatus::Active)
            ->orderBy('name_en');

        $isSuperAdmin = $user?->hasRole('Super Admin') || $user?->hasRole('City Admin');
        if (! $isSuperAdmin && $user !== null) {
            $allowedIds = $this->orgScope->accessibleOrganizationIds($user);
            if ($allowedIds->isNotEmpty()) {
                $query->whereIn('id', $allowedIds);
            }
        }

        return $query->get(['id', 'name_en', 'name_am', 'code'])->toArray();
    }
}
