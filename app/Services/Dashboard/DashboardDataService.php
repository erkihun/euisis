<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardDataService
{
    public function __construct(
        private readonly DashboardScopeService $scopeService,
        private readonly DashboardMetricService $metricService,
        private readonly DashboardChartService $chartService,
    ) {}

    public function build(User $user, array $filters): array
    {
        $scope = $this->scopeService->resolve($user, $filters);
        $can = $this->sectionPermissions($user);

        return Cache::remember(
            $this->cacheKey($user, $scope, $can),
            now()->addSeconds(120),
            fn (): array => [
                'filters' => [
                    'dateRange' => $scope['date_range'],
                    'dateFrom' => $scope['date_from']->toDateString(),
                    'dateTo' => $scope['date_to']->toDateString(),
                    'organizationId' => $scope['selected_organization_id'],
                    'organizationOptions' => $scope['organization_options'],
                ],
                'can' => $can,
                'kpis' => $this->metricService->kpis($user, $scope, $can),
                'cards' => $this->metricService->sectionCards($scope, $can),
                'charts' => $this->chartService->charts($scope, $can),
                'workflowQueues' => $this->metricService->workflowQueues($scope, $can),
                'alerts' => $this->metricService->alerts($scope, $can),
                'recentActivity' => $this->metricService->recentActivity($scope, $can['audit']),
                'meta' => [
                    'scope' => [
                        'providerOnly' => $scope['provider_only'],
                        'globalAccess' => $scope['global_access'],
                    ],
                ],
            ],
        );
    }

    public function canViewDashboard(User $user): bool
    {
        return $this->hasAnyPermission($user, [
            'dashboard.view',
            'reports.view',
            'employees.view',
            'employees.viewAny',
            'organizations.view',
            'organizations.viewAny',
            'transactions.view',
            'service-transactions.viewAny',
            'cards.view',
            'id-cards.viewAny',
        ]);
    }

    public function sectionPermissions(User $user): array
    {
        return [
            'dashboard' => $this->canViewDashboard($user),
            'employees' => $this->hasAnyPermission($user, ['employees.viewAny', 'employees.view', 'employees.manage']),
            'organizations' => $this->hasAnyPermission($user, ['organizations.viewAny', 'organizations.view', 'organizations.manage']),
            'cards' => $this->hasAnyPermission($user, ['id-cards.viewAny', 'id-cards.view', 'cards.view']),
            'verification' => $this->hasAnyPermission($user, ['id-cards.verify', 'card-verifications.viewAny', 'id-cards.viewAny', 'cards.view']),
            'entitlements' => $this->hasAnyPermission($user, ['entitlements.viewAny', 'entitlements.view', 'entitlements.manage']),
            'transactions' => $this->hasAnyPermission($user, ['service-transactions.viewAny', 'transactions.view', 'transactions.manage']),
            'providers' => $this->hasAnyPermission($user, ['providers.viewAny', 'transactions.view', 'transactions.manage']),
            'transfers' => $this->hasAnyPermission($user, ['transfers.viewAny', 'transfers.view']),
            'audit' => $this->hasAnyPermission($user, ['audit-logs.viewAny', 'audit.view', 'reports.view']),
        ];
    }

    private function hasAnyPermission(User $user, array $permissions): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    private function cacheKey(User $user, array $scope, array $can): string
    {
        return 'dashboard:'.md5(json_encode([
            'user' => $user->id,
            'date_range' => $scope['date_range'],
            'date_from' => $scope['date_from']->toDateString(),
            'date_to' => $scope['date_to']->toDateString(),
            'organization_ids' => $scope['organization_ids'],
            'provider_ids' => $scope['provider_ids'],
            'service_type_ids' => $scope['service_type_ids'],
            'can' => $can,
        ], JSON_THROW_ON_ERROR));
    }
}
