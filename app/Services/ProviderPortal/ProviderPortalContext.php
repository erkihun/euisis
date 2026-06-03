<?php

declare(strict_types=1);

namespace App\Services\ProviderPortal;

use App\Models\CafeteriaProvider;
use App\Models\Provider;
use App\Models\ProviderUser;
use Illuminate\Http\Request;

class ProviderPortalContext
{
    public function providerUser(Request $request): ?ProviderUser
    {
        /** @var ProviderUser|null $providerUser */
        $providerUser = auth('provider')->user();

        return $providerUser;
    }

    public function provider(Request $request): ?Provider
    {
        $provider = $this->providerUser($request)?->provider;

        return $provider instanceof Provider && $provider->status === 'active'
            ? $provider
            : null;
    }

    public function selectedProvider(Request $request): ?CafeteriaProvider
    {
        $provider = $this->provider($request);

        if ($provider === null || ! $provider->hasService('cafeteria')) {
            return null;
        }

        $cafeteriaProvider = $provider->cafeteriaProvider;

        return $cafeteriaProvider instanceof CafeteriaProvider && $cafeteriaProvider->is_active
            ? $cafeteriaProvider
            : null;
    }

    /** @return array<string, mixed> */
    public function providerPayload(Request $request): array
    {
        $provider = $this->provider($request);
        $cafeteriaProvider = $this->selectedProvider($request);

        return [
            'providers' => $cafeteriaProvider ? [$this->formatCafeteriaProvider($cafeteriaProvider)] : [],
            'selected_provider_id' => $cafeteriaProvider?->id,
            'providerPortal' => $provider ? $this->formatProviderPortal($request, $provider) : null,
        ];
    }

    /** @return array<string, mixed> */
    private function formatCafeteriaProvider(CafeteriaProvider $provider): array
    {
        return [
            'id' => $provider->id,
            'code' => $provider->code,
            'name_en' => $provider->name_en,
            'name_am' => $provider->name_am,
            'location' => $provider->location,
        ];
    }

    /** @return array<string, mixed> */
    private function formatProviderPortal(Request $request, Provider $provider): array
    {
        $providerUser = $this->providerUser($request);
        $provider->loadMissing(['providerType', 'services.serviceType']);

        return [
            'isPortal' => true,
            'user' => $providerUser ? [
                'id' => $providerUser->id,
                'name' => $providerUser->name,
                'email' => $providerUser->email,
                'username' => $providerUser->username,
                'initials' => $providerUser->initials(),
                'status' => $providerUser->status,
                'provider_role' => $providerUser->provider_role,
                'must_change_password' => $providerUser->must_change_password,
            ] : null,
            'provider' => [
                'id' => $provider->id,
                'code' => $provider->provider_code,
                'name_en' => $provider->name_en,
                'name_am' => $provider->name_am,
                'logo_url' => $provider->logo_path ? asset('storage/'.$provider->logo_path) : null,
                'type' => $provider->providerType?->code,
            ],
            'enabledServices' => $provider->services
                ->filter(fn ($service): bool => $service->status === 'active' && (bool) $service->serviceType?->is_active)
                ->map(fn ($service): array => [
                    'code' => $service->serviceType->code,
                    'name' => $service->serviceType->name_en,
                    'route' => $service->serviceType->route_prefix,
                    'icon' => $service->serviceType->icon,
                ])
                ->values()
                ->all(),
            'can' => [
                'cafeteriaScan' => $providerUser?->hasService('cafeteria') ?? false,
                'cafeteriaTransactionsView' => $providerUser?->hasService('cafeteria') ?? false,
                'cafeteriaTransactionsExport' => $providerUser?->hasService('cafeteria') ?? false,
                'cafeteriaMenusManage' => $providerUser?->hasService('cafeteria') ?? false,
                'cafeteriaOrdersManage' => $providerUser?->hasService('cafeteria') ?? false,
                'transportDashboardView' => $providerUser?->hasService('transport') ?? false,
                'transportScan' => $providerUser?->hasService('transport') ?? false,
                'transportTransactionsView' => $providerUser?->hasService('transport') ?? false,
                'transportRoutesManage' => $providerUser?->hasService('transport') ?? false,
                'transportVehiclesManage' => $providerUser?->hasService('transport') ?? false,
                'transportDriversManage' => $providerUser?->hasService('transport') ?? false,
                'transportTripsManage' => $providerUser?->hasService('transport') ?? false,
                'transportReportsView' => $providerUser?->hasService('transport') ?? false,
                'reportsView' => $providerUser?->hasService('cafeteria') ?? false,
            ],
        ];
    }
}
