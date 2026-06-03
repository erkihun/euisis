<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\CardRequestStatus;
use App\Enums\CardStatus;
use App\Enums\EmployeeStatus;
use App\Enums\EntitlementStatus;
use App\Enums\TransferStatus;
use App\Models\AuditLog;
use App\Models\CardPrintBatch;
use App\Models\CardRequest;
use App\Models\CardVerification;
use App\Models\Employee;
use App\Models\EmployeeDuplicateFlag;
use App\Models\EmployeeTransfer;
use App\Models\Entitlement;
use App\Models\HierarchyVersion;
use App\Models\IdCard;
use App\Models\Organization;
use App\Models\Position;
use App\Models\ServiceProvider;
use App\Models\ServiceTransaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class DashboardMetricService
{
    public function kpis(User $user, array $scope, array $can): array
    {
        $kpis = [];

        if ($can['employees']) {
            $registeredEmployees = $this->employeeQuery($scope)->count();
            $activeEmployees = $this->employeeQuery($scope)
                ->where('employees.status', EmployeeStatus::Active->value)
                ->count();

            $kpis[] = $this->kpi('activeEmployees', $activeEmployees, 'users', 'primary', $this->employeeTrend($scope, EmployeeStatus::Active->value));
            $kpis[] = $this->kpi('registeredEmployees', $registeredEmployees, 'users', 'neutral', $this->employeeTrend($scope));
            $kpis[] = $this->kpi('dataQualityWarnings', $this->duplicateWarningsCount($scope), 'alert', 'warning');
        }

        if ($can['organizations']) {
            $kpis[] = $this->kpi(
                'activeOrganizations',
                $this->organizationQuery($scope)->where('organizations.status', 'active')->count(),
                'building',
                'neutral',
            );
        }

        if ($can['positions'] ?? false) {
            $kpis[] = $this->kpi(
                'totalPositions',
                $this->positionQuery($scope)->count(),
                'layers',
                'primary',
            );
            $kpis[] = $this->kpi(
                'activePositions',
                $this->positionQuery($scope)->where('positions.is_active', true)->count(),
                'layers',
                'success',
            );
            $kpis[] = $this->kpi(
                'vacantPositions',
                $this->vacantPositionsCount($scope),
                'alert',
                'warning',
            );
        }

        if ($can['cards']) {
            $activeCards = $this->cardQuery($scope)
                ->where('id_cards.status', CardStatus::Active->value)
                ->count();
            $pendingCardRequests = $this->cardRequestQuery($scope)
                ->where('card_requests.status', CardRequestStatus::Submitted->value)
                ->count();
            $pendingApprovals = $this->cardRequestQuery($scope)
                ->whereIn('card_requests.status', [CardRequestStatus::Submitted->value, CardRequestStatus::Verified->value])
                ->count();
            $employeeBase = max($this->employeeQuery($scope)->count(), 1);
            $coverage = round(($activeCards / $employeeBase) * 100, 2);

            $kpis[] = $this->kpi('activeIdCards', $activeCards, 'card', 'primary', $this->cardTrend($scope, CardStatus::Active->value));
            $kpis[] = $this->kpi('pendingCardRequests', $pendingCardRequests, 'queue', 'warning');
            $kpis[] = $this->kpi('pendingApprovals', $pendingApprovals, 'queue', 'warning');
            $kpis[] = $this->kpi('cardCoverage', $coverage, 'coverage', $coverage >= 80 ? 'success' : 'warning', null, '%');
        }

        if ($can['entitlements']) {
            $kpis[] = $this->kpi(
                'activeEntitlements',
                $this->entitlementQuery($scope)->where('entitlements.status', EntitlementStatus::Active->value)->count(),
                'layers',
                'success',
            );
        }

        if ($can['verification']) {
            $kpis[] = $this->kpi(
                'todayVerifications',
                $this->verificationQuery($scope)
                    ->whereDate('card_verifications.created_at', now()->toDateString())
                    ->count(),
                'shield',
                'primary',
            );
        }

        if ($can['transactions']) {
            $kpis[] = $this->kpi(
                'todayTransactions',
                $this->transactionQuery($scope)
                    ->whereDate('service_transactions.occurred_at', now()->toDateString())
                    ->count(),
                'activity',
                'primary',
            );
        }

        if ($can['transfers']) {
            $kpis[] = $this->kpi(
                'pendingTransfers',
                $this->transferQuery($scope)
                    ->whereIn('employee_transfers.status', [
                        TransferStatus::Submitted->value,
                        TransferStatus::CurrentOrganizationConfirmed->value,
                        TransferStatus::ReceivingOrganizationConfirmed->value,
                    ])->count(),
                'transfer',
                'warning',
            );
        }

        return $kpis;
    }

    public function sectionCards(array $scope, array $can): array
    {
        $cards = [];

        if ($can['employees']) {
            $cards['employees'] = [
                'duplicateWarnings' => $this->duplicateWarningsCount($scope),
                'missingPhotoCount' => $this->employeeQuery($scope)->whereNull('employees.photo_path')->count(),
                'missingDocumentCount' => $this->employeeQuery($scope)->doesntHave('documents')->count(),
                'averageDataQualityScore' => round((float) $this->employeeQuery($scope)->avg('employees.data_quality_score'), 2),
            ];
        }

        if ($can['organizations']) {
            $cards['organizations'] = [
                'draftHierarchyVersions' => HierarchyVersion::query()->where('status', 'draft')->count(),
                'activeHierarchyVersion' => HierarchyVersion::query()
                    ->where('status', 'published')
                    ->orderByDesc('effective_from')
                    ->value('version_name'),
                'missingMetadataCount' => $this->organizationQuery($scope)
                    ->where(function (Builder $query): void {
                        $query->whereNull('organizations.legal_basis_ref')
                            ->orWhereNull('organizations.name_am');
                    })->count(),
            ];
        }

        if ($can['positions'] ?? false) {
            $cards['positions'] = [
                'totalPositions' => $this->positionQuery($scope)->count(),
                'activePositions' => $this->positionQuery($scope)->where('positions.is_active', true)->count(),
                'vacantPositions' => $this->vacantPositionsCount($scope),
                'positionsWithOccupation' => $this->positionQuery($scope)
                    ->whereNotNull('positions.occupation_id')
                    ->count(),
            ];
        }

        if ($can['cards']) {
            $cards['cards'] = [
                'expiringSoonCount' => $this->cardQuery($scope)
                    ->whereNotNull('id_cards.expires_at')
                    ->whereBetween('id_cards.expires_at', [now(), now()->addDays(30)])
                    ->count(),
                'pendingPrintBatches' => CardPrintBatch::query()->where('status', 'pending')->count(),
                'duplicateActiveCardWarnings' => $this->duplicateActiveCardWarnings($scope),
            ];
        }

        if ($can['verification']) {
            $cards['verification'] = [
                'allowedToday' => $this->verificationQuery($scope)
                    ->whereDate('card_verifications.created_at', now()->toDateString())
                    ->where('card_verifications.allowed', true)
                    ->count(),
                'deniedToday' => $this->verificationQuery($scope)
                    ->whereDate('card_verifications.created_at', now()->toDateString())
                    ->where('card_verifications.allowed', false)
                    ->count(),
            ];
        }

        if ($can['entitlements']) {
            $cards['entitlements'] = [
                'expiringSoonCount' => $this->entitlementQuery($scope)
                    ->whereNotNull('entitlements.effective_to')
                    ->whereBetween('entitlements.effective_to', [now()->toDateString(), now()->addDays(30)->toDateString()])
                    ->count(),
                'exhaustedCount' => $this->entitlementQuery($scope)
                    ->where('entitlements.status', EntitlementStatus::Exhausted->value)
                    ->count(),
            ];
        }

        if ($can['providers']) {
            $cards['providers'] = [
                'activeProviders' => $this->providerQuery($scope)
                    ->where('service_providers.status', 'active')
                    ->count(),
            ];
        }

        return $cards;
    }

    public function workflowQueues(array $scope, array $can): array
    {
        $items = [];

        if ($can['cards']) {
            $items[] = $this->queue('awaitingVerification', 'card-requests.index', $this->cardRequestQuery($scope)
                ->where('card_requests.status', CardRequestStatus::Submitted->value)
                ->count(), 'warning');
            $items[] = $this->queue('awaitingApproval', 'card-requests.index', $this->cardRequestQuery($scope)
                ->where('card_requests.status', CardRequestStatus::Verified->value)
                ->count(), 'warning');
            $items[] = $this->queue('awaitingIssuance', 'id-cards.index', $this->cardQuery($scope)
                ->where('id_cards.status', CardStatus::Printed->value)
                ->count(), 'primary');
        }

        if ($can['transfers']) {
            $items[] = $this->queue('transfersPendingConfirmation', 'transfers.dashboard', $this->transferQuery($scope)
                ->whereIn('employee_transfers.status', [
                    TransferStatus::Submitted->value,
                    TransferStatus::CurrentOrganizationConfirmed->value,
                    TransferStatus::ReceivingOrganizationConfirmed->value,
                ])->count(), 'warning');
        }

        if ($can['organizations']) {
            $items[] = $this->queue('hierarchyVersionsAwaitingPublish', 'organizations.index', HierarchyVersion::query()
                ->where('status', 'draft')
                ->count(), 'neutral');
        }

        return array_values(array_filter($items, fn (array $item): bool => $item['count'] > 0));
    }

    public function alerts(array $scope, array $can): array
    {
        $alerts = [];

        if ($can['cards']) {
            $expiringCards = $this->cardQuery($scope)
                ->whereBetween('id_cards.expires_at', [now(), now()->addDays(30)])
                ->count();

            if ($expiringCards > 0) {
                $alerts[] = $this->alert('cardsExpiringSoon', 'warning', $expiringCards, route('id-cards.index'));
            }

            $expiredCards = $this->cardQuery($scope)
                ->where('id_cards.status', CardStatus::Expired->value)
                ->count();

            if ($expiredCards > 0) {
                $alerts[] = $this->alert('expiredCardsPendingRenewal', 'critical', $expiredCards, route('id-cards.index'));
            }
        }

        if ($can['employees']) {
            $employeesWithoutCard = $this->employeeQuery($scope)
                ->whereDoesntHave('cardRequests.cards', fn (Builder $query) => $query->where('is_current', true))
                ->count();

            if ($employeesWithoutCard > 0) {
                $alerts[] = $this->alert('employeesWithoutIdCard', 'warning', $employeesWithoutCard, route('employees.index'));
            }

            $duplicateWarnings = $this->duplicateWarningsCount($scope);

            if ($duplicateWarnings > 0) {
                $alerts[] = $this->alert('duplicateRisk', 'warning', $duplicateWarnings, route('employees.index'));
            }
        }

        if ($can['transfers']) {
            $staleTransfers = $this->transferQuery($scope)
                ->whereIn('employee_transfers.status', [
                    TransferStatus::Submitted->value,
                    TransferStatus::CurrentOrganizationConfirmed->value,
                    TransferStatus::ReceivingOrganizationConfirmed->value,
                ])
                ->where('employee_transfers.created_at', '<=', now()->subDays(7))
                ->count();

            if ($staleTransfers > 0) {
                $alerts[] = $this->alert('staleTransferApprovals', 'critical', $staleTransfers, route('transfers.dashboard'));
            }
        }

        if ($can['audit']) {
            $verificationSpike = $this->verificationQuery($scope)
                ->where('card_verifications.allowed', false)
                ->where('card_verifications.created_at', '>=', now()->subDay())
                ->count();

            if ($verificationSpike > 10) {
                $alerts[] = $this->alert('verificationDenialSpike', 'critical', $verificationSpike, route('audit-logs.index'));
            }
        }

        return $alerts;
    }

    public function recentActivity(array $scope, bool $canAudit): array
    {
        if (! $canAudit) {
            return [];
        }

        $logs = AuditLog::query()
            ->when(
                ! $scope['global_access'] && $scope['organization_ids'] !== [],
                fn (Builder $query) => $query->whereIn('organization_id', $scope['organization_ids'])
            )
            ->when(
                ! $scope['global_access'] && $scope['organization_ids'] === [],
                fn (Builder $query) => $query->whereRaw('1 = 0')
            )
            ->orderByDesc('created_at')
            ->limit($scope['activity_limit'])
            ->get(['id', 'event_type', 'auditable_type', 'actor_user_id', 'created_at']);

        // Batch-resolve actor display names
        $actorIds = $logs->pluck('actor_user_id')->filter()->unique()->values();
        $actorNames = $actorIds->isNotEmpty()
            ? User::query()->whereIn('id', $actorIds)->pluck('name', 'id')
            : collect();

        return $logs->map(fn (AuditLog $log): array => [
            'id'        => $log->id,
            'event'     => $log->event_type->value,
            'actor'     => $log->actor_user_id
                ? ($actorNames->get($log->actor_user_id) ?? $log->actor_user_id)
                : 'system',
            'subject'   => $log->auditable_type ? class_basename($log->auditable_type) : null,
            'timestamp' => $log->created_at?->toIso8601String(),
            'severity'  => $this->severityForAuditEvent($log->event_type->value),
        ])->all();
    }

    private function kpi(string $key, int|float $value, string $icon, string $tone, ?array $trend = null, string $suffix = ''): array
    {
        return [
            'key' => $key,
            'labelKey' => "dashboard.kpis.{$key}",
            'value' => $value,
            'valueFormatted' => $suffix !== '' ? "{$value}{$suffix}" : (string) $value,
            'trend' => $trend['delta'] ?? null,
            'trendDirection' => $trend['direction'] ?? null,
            'comparisonLabelKey' => $trend !== null ? 'dashboard.previousPeriod' : null,
            'icon' => $icon,
            'tone' => $tone,
        ];
    }

    private function queue(string $key, string $routeName, int $count, string $tone): array
    {
        return [
            'key' => $key,
            'labelKey' => "dashboard.workflow.{$key}",
            'count' => $count,
            'href' => route($routeName),
            'tone' => $tone,
        ];
    }

    private function alert(string $key, string $severity, int $count, string $href): array
    {
        return [
            'key' => $key,
            'titleKey' => "dashboard.alerts.{$key}.title",
            'descriptionKey' => "dashboard.alerts.{$key}.description",
            'severity' => $severity,
            'count' => $count,
            'href' => $href,
        ];
    }

    private function employeeTrend(array $scope, ?string $status = null): array
    {
        $current = $this->employeeTrendCount($scope, $scope['date_from'], $scope['date_to'], $status);
        $previous = $this->employeeTrendCount($scope, $scope['previous_from'], $scope['previous_to'], $status);

        return $this->trend($current, $previous);
    }

    private function cardTrend(array $scope, ?string $status = null): array
    {
        $current = $this->cardTrendCount($scope, $scope['date_from'], $scope['date_to'], $status);
        $previous = $this->cardTrendCount($scope, $scope['previous_from'], $scope['previous_to'], $status);

        return $this->trend($current, $previous);
    }

    private function trend(int $current, int $previous): array
    {
        $delta = $current - $previous;

        return [
            'delta' => $delta,
            'direction' => $delta === 0 ? 'flat' : ($delta > 0 ? 'up' : 'down'),
        ];
    }

    private function employeeTrendCount(array $scope, CarbonImmutable $from, CarbonImmutable $to, ?string $status = null): int
    {
        return $this->employeeQuery($scope)
            ->when($status !== null, fn (Builder $query) => $query->where('employees.status', $status))
            ->whereBetween('employees.created_at', [$from, $to])
            ->count();
    }

    private function cardTrendCount(array $scope, CarbonImmutable $from, CarbonImmutable $to, ?string $status = null): int
    {
        return $this->cardQuery($scope)
            ->when($status !== null, fn (Builder $query) => $query->where('id_cards.status', $status))
            ->whereBetween('id_cards.created_at', [$from, $to])
            ->count();
    }

    private function duplicateWarningsCount(array $scope): int
    {
        return EmployeeDuplicateFlag::query()
            ->join('employees', 'employees.id', '=', 'employee_duplicate_flags.employee_id')
            ->when(
                ! $scope['global_access'] && $scope['organization_ids'] !== [],
                function (Builder $query) use ($scope): void {
                    $query->join('employee_assignments as employee_duplicate_scope_assignment', 'employee_duplicate_scope_assignment.id', '=', 'employees.current_assignment_id')
                        ->whereIn('employee_duplicate_scope_assignment.organization_id', $scope['organization_ids']);
                }
            )
            ->when(
                ! $scope['global_access'] && $scope['organization_ids'] === [],
                fn (Builder $query) => $query->whereRaw('1 = 0')
            )
            ->count();
    }

    private function duplicateActiveCardWarnings(array $scope): int
    {
        return $this->cardQuery($scope)
            ->select('id_cards.employee_id')
            ->whereIn('id_cards.status', [CardStatus::Active->value, CardStatus::Issued->value])
            ->groupBy('id_cards.employee_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();
    }

    private function vacantPositionsCount(array $scope): int
    {
        return $this->positionQuery($scope)
            ->withCount('assignments')
            ->get()
            ->where('assignments_count', 0)
            ->count();
    }

    private function severityForAuditEvent(string $event): string
    {
        if (str_contains($event, 'rejected') || str_contains($event, 'denied')) {
            return 'warning';
        }

        if (str_contains($event, 'revoked') || str_contains($event, 'security')) {
            return 'critical';
        }

        return 'info';
    }

    public function employeeQuery(array $scope): Builder
    {
        return Employee::query()
            ->when(
                ! $scope['global_access'] && $scope['organization_ids'] !== [],
                function (Builder $query) use ($scope): void {
                    $query->join('employee_assignments as employee_scope_assignment', 'employee_scope_assignment.id', '=', 'employees.current_assignment_id')
                        ->whereIn('employee_scope_assignment.organization_id', $scope['organization_ids']);
                }
            )
            ->when(
                ! $scope['global_access'] && $scope['organization_ids'] === [],
                fn (Builder $query) => $query->whereRaw('1 = 0')
            );
    }

    public function organizationQuery(array $scope): Builder
    {
        return Organization::query()
            ->when(
                ! $scope['global_access'] && $scope['organization_ids'] !== [],
                fn (Builder $query) => $query->whereIn('organizations.id', $scope['organization_ids'])
            )
            ->when(
                ! $scope['global_access'] && $scope['organization_ids'] === [],
                fn (Builder $query) => $query->whereRaw('1 = 0')
            );
    }

    public function positionQuery(array $scope): Builder
    {
        return Position::query()
            ->when(
                ! $scope['global_access'] && ! empty($scope['organization_ids']),
                fn (Builder $query) => $query->whereIn('positions.organization_id', $scope['organization_ids'])
            )
            ->when(
                ! $scope['global_access'] && empty($scope['organization_ids']),
                fn (Builder $query) => $query->whereRaw('1 = 0')
            );
    }

    public function cardQuery(array $scope): Builder
    {
        return IdCard::query()
            ->join('employees as card_employee', 'card_employee.id', '=', 'id_cards.employee_id')
            ->leftJoin('employee_assignments as card_scope_assignment', 'card_scope_assignment.id', '=', 'card_employee.current_assignment_id')
            ->when(
                ! $scope['global_access'] && ! $scope['provider_only'] && $scope['organization_ids'] !== [],
                fn (Builder $query) => $query->whereIn('card_scope_assignment.organization_id', $scope['organization_ids'])
            )
            ->when(
                ! $scope['global_access'] && ! $scope['provider_only'] && $scope['organization_ids'] === [],
                fn (Builder $query) => $query->whereRaw('1 = 0')
            );
    }

    public function cardRequestQuery(array $scope): Builder
    {
        return CardRequest::query()
            ->join('employees as card_request_employee', 'card_request_employee.id', '=', 'card_requests.employee_id')
            ->leftJoin('employee_assignments as card_request_scope_assignment', 'card_request_scope_assignment.id', '=', 'card_request_employee.current_assignment_id')
            ->when(
                ! $scope['global_access'] && ! $scope['provider_only'] && $scope['organization_ids'] !== [],
                fn (Builder $query) => $query->whereIn('card_request_scope_assignment.organization_id', $scope['organization_ids'])
            )
            ->when(
                ! $scope['global_access'] && ! $scope['provider_only'] && $scope['organization_ids'] === [],
                fn (Builder $query) => $query->whereRaw('1 = 0')
            );
    }

    public function entitlementQuery(array $scope): Builder
    {
        return Entitlement::query()
            ->join('employees as entitlement_employee', 'entitlement_employee.id', '=', 'entitlements.employee_id')
            ->leftJoin('employee_assignments as entitlement_scope_assignment', 'entitlement_scope_assignment.id', '=', 'entitlement_employee.current_assignment_id')
            ->when(
                ! $scope['global_access'] && ! $scope['provider_only'] && $scope['organization_ids'] !== [],
                fn (Builder $query) => $query->whereIn('entitlement_scope_assignment.organization_id', $scope['organization_ids'])
            )
            ->when(
                ! $scope['global_access'] && ! $scope['provider_only'] && $scope['organization_ids'] === [],
                fn (Builder $query) => $query->whereRaw('1 = 0')
            )
            ->when(
                $scope['provider_only'] && $scope['provider_ids'] === [],
                fn (Builder $query) => $query->whereRaw('1 = 0')
            )
            ->when($scope['provider_ids'] !== [], fn (Builder $query) => $query->whereIn('entitlements.service_provider_id', $scope['provider_ids']))
            ->when($scope['service_type_ids'] !== [], fn (Builder $query) => $query->whereIn('entitlements.service_type_id', $scope['service_type_ids']));
    }

    public function transactionQuery(array $scope): Builder
    {
        return ServiceTransaction::query()
            ->leftJoin('employees as service_transaction_employee', 'service_transaction_employee.id', '=', 'service_transactions.employee_id')
            ->leftJoin('employee_assignments as service_transaction_scope_assignment', 'service_transaction_scope_assignment.id', '=', 'service_transaction_employee.current_assignment_id')
            ->when(
                ! $scope['global_access'] && ! $scope['provider_only'] && $scope['organization_ids'] !== [],
                fn (Builder $query) => $query->whereIn('service_transaction_scope_assignment.organization_id', $scope['organization_ids'])
            )
            ->when(
                ! $scope['global_access'] && ! $scope['provider_only'] && $scope['organization_ids'] === [],
                fn (Builder $query) => $query->whereRaw('1 = 0')
            )
            ->when(
                $scope['provider_only'] && $scope['provider_ids'] === [],
                fn (Builder $query) => $query->whereRaw('1 = 0')
            )
            ->when($scope['provider_ids'] !== [], fn (Builder $query) => $query->whereIn('service_transactions.service_provider_id', $scope['provider_ids']))
            ->when($scope['service_type_ids'] !== [], fn (Builder $query) => $query->whereIn('service_transactions.service_type_id', $scope['service_type_ids']));
    }

    public function verificationQuery(array $scope): Builder
    {
        return CardVerification::query()
            ->leftJoin('service_providers as verification_provider', 'verification_provider.id', '=', 'card_verifications.service_provider_id')
            ->leftJoin('id_cards as verification_card', 'verification_card.id', '=', 'card_verifications.id_card_id')
            ->leftJoin('employees as verification_employee', 'verification_employee.id', '=', 'verification_card.employee_id')
            ->leftJoin('employee_assignments as verification_scope_assignment', 'verification_scope_assignment.id', '=', 'verification_employee.current_assignment_id')
            ->when(
                ! $scope['global_access'] && ! $scope['provider_only'] && $scope['organization_ids'] !== [],
                fn (Builder $query) => $query->whereIn('verification_scope_assignment.organization_id', $scope['organization_ids'])
            )
            ->when(
                ! $scope['global_access'] && ! $scope['provider_only'] && $scope['organization_ids'] === [],
                fn (Builder $query) => $query->whereRaw('1 = 0')
            )
            ->when(
                $scope['provider_only'] && $scope['provider_ids'] === [],
                fn (Builder $query) => $query->whereRaw('1 = 0')
            )
            ->when($scope['provider_ids'] !== [], fn (Builder $query) => $query->whereIn('card_verifications.service_provider_id', $scope['provider_ids']))
            ->when($scope['service_type_ids'] !== [], fn (Builder $query) => $query->whereIn('card_verifications.service_type_id', $scope['service_type_ids']));
    }

    public function providerQuery(array $scope): Builder
    {
        return ServiceProvider::query()
            ->when(
                ! $scope['global_access'] && ! $scope['provider_only'] && $scope['organization_ids'] !== [],
                fn (Builder $query) => $query->whereIn('service_providers.organization_id', $scope['organization_ids'])
            )
            ->when(
                ! $scope['global_access'] && ! $scope['provider_only'] && $scope['organization_ids'] === [],
                fn (Builder $query) => $query->whereRaw('1 = 0')
            )
            ->when(
                $scope['provider_only'] && $scope['provider_ids'] === [],
                fn (Builder $query) => $query->whereRaw('1 = 0')
            )
            ->when($scope['provider_ids'] !== [], fn (Builder $query) => $query->whereIn('service_providers.id', $scope['provider_ids']))
            ->when($scope['service_type_ids'] !== [], fn (Builder $query) => $query->whereIn('service_providers.service_type_id', $scope['service_type_ids']));
    }

    public function transferQuery(array $scope): Builder
    {
        return EmployeeTransfer::query()
            ->when(
                ! $scope['global_access'] && $scope['organization_ids'] !== [],
                function (Builder $query) use ($scope): void {
                    $query->where(function (Builder $nested) use ($scope): void {
                        $nested->whereIn('employee_transfers.from_organization_id', $scope['organization_ids'])
                            ->orWhereIn('employee_transfers.to_organization_id', $scope['organization_ids']);
                    });
                }
            )
            ->when(
                ! $scope['global_access'] && $scope['organization_ids'] === [],
                fn (Builder $query) => $query->whereRaw('1 = 0')
            );
    }
}
