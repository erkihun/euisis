<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceProviderRequest;
use App\Http\Requests\UpdateServiceProviderRequest;
use App\Models\Organization;
use App\Models\ServiceProvider;
use App\Models\ServiceTransaction;
use App\Models\ServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceProviderController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', ServiceProvider::class);

        return Inertia::render('ServiceProviders/Index', [
            'serviceTypes' => ServiceType::query()->orderBy('name_en')->get(),
            'providers'    => ServiceProvider::query()->with(['serviceType', 'organization'])->orderBy('name')->get(),
            'transactions' => ServiceTransaction::query()
                ->with(['serviceProvider', 'serviceType'])
                ->orderByDesc('occurred_at')
                ->limit(50)
                ->get(),
            'can' => [
                'create' => request()->user()?->can('create', ServiceProvider::class) ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', ServiceProvider::class);

        return Inertia::render('ServiceProviders/Create', [
            'serviceTypes'  => ServiceType::query()->orderBy('name_en')->get(['id', 'name_en']),
            'organizations' => Organization::query()->orderBy('name_en')->get(['id', 'name_en']),
        ]);
    }

    public function store(StoreServiceProviderRequest $request): RedirectResponse
    {
        $provider = ServiceProvider::query()->create($request->validated());

        return to_route('service-providers.show', $provider)
            ->with('flash', ['message' => __('providers.created'), 'type' => 'success']);
    }

    public function show(ServiceProvider $serviceProvider): Response
    {
        $this->authorize('view', $serviceProvider);

        return Inertia::render('ServiceProviders/Show', [
            'provider'     => $serviceProvider->load(['serviceType', 'organization', 'transactions.serviceType']),
            'transactions' => $serviceProvider->transactions()->with('serviceType')->orderByDesc('occurred_at')->limit(100)->get(),
            'can' => [
                'update' => request()->user()?->can('update', $serviceProvider) ?? false,
                'delete' => request()->user()?->can('delete', $serviceProvider) ?? false,
            ],
        ]);
    }

    public function edit(ServiceProvider $serviceProvider): Response
    {
        $this->authorize('update', $serviceProvider);

        return Inertia::render('ServiceProviders/Edit', [
            'provider'      => $serviceProvider->load(['serviceType', 'organization']),
            'serviceTypes'  => ServiceType::query()->orderBy('name_en')->get(['id', 'name_en']),
            'organizations' => Organization::query()->orderBy('name_en')->get(['id', 'name_en']),
        ]);
    }

    public function update(UpdateServiceProviderRequest $request, ServiceProvider $serviceProvider): RedirectResponse
    {
        $serviceProvider->update($request->validated());

        return to_route('service-providers.show', $serviceProvider)
            ->with('flash', ['message' => __('providers.updated'), 'type' => 'success']);
    }

    public function destroy(Request $request, ServiceProvider $serviceProvider): RedirectResponse
    {
        $this->authorize('delete', $serviceProvider);

        $serviceProvider->delete();

        return to_route('service-providers.index')
            ->with('flash', ['message' => __('providers.deleted'), 'type' => 'success']);
    }
}
