<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use App\Models\ServiceTransaction;
use App\Models\ServiceType;
use Inertia\Inertia;
use Inertia\Response;

class ServiceProviderController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('ServiceProviders/Index', [
            'serviceTypes' => ServiceType::query()->orderBy('name_en')->get(),
            'providers' => ServiceProvider::query()->with(['serviceType', 'organization'])->get(),
            'transactions' => ServiceTransaction::query()
                ->with(['serviceProvider', 'serviceType'])
                ->orderByDesc('occurred_at')
                ->limit(50)
                ->get(),
        ]);
    }

    public function show(ServiceProvider $serviceProvider): Response
    {
        return Inertia::render('ServiceProviders/Show', [
            'provider' => $serviceProvider->load(['serviceType', 'organization', 'transactions.serviceType']),
            'transactions' => $serviceProvider->transactions()->with('serviceType')->orderByDesc('occurred_at')->limit(100)->get(),
        ]);
    }
}
