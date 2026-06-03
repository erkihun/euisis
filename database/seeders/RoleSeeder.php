<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPermissions = Permission::all()->pluck('name')->toArray();

        $institutionAdminPerms = [
            'dashboard.view',
            'organizations.view', 'organizations.manage', 'organizations.viewAny',
            'organization-types.viewAny', 'organization-types.view',
            'hierarchy-versions.viewAny', 'hierarchy-versions.view',
            'organization-edges.view',
            'service-types.viewAny', 'service-types.view',
            'entitlement-rules.viewAny', 'entitlement-rules.view',
            'code-rules.viewAny', 'code-rules.view', 'code-rules.preview',
            'employees.view', 'employees.viewAny', 'employees.manage',
            'cards.view', 'cards.manage',
            'id-cards.viewAny',
            'audit.view', 'reports.view',
        ];

        $roleMap = [
            'Super Admin' => $allPermissions,
            'Public Service Bureau Admin' => $allPermissions,
            'City Admin' => $allPermissions,
            'Institution Admin' => $institutionAdminPerms,
            'Sub-city Admin' => [
                'dashboard.view', 'organizations.view', 'organizations.viewAny',
                'organization-types.viewAny', 'service-types.viewAny',
                'entitlement-rules.viewAny', 'employees.view', 'employees.viewAny',
                'employees.manage', 'cards.view', 'id-cards.viewAny', 'audit.view', 'reports.view',
            ],
            'Woreda Admin' => [
                'dashboard.view', 'organizations.view', 'organizations.viewAny',
                'organization-types.viewAny', 'service-types.viewAny',
                'entitlement-rules.viewAny', 'employees.view', 'employees.viewAny',
                'employees.manage', 'cards.view', 'id-cards.viewAny', 'reports.view',
            ],
            'HR Officer' => [
                'dashboard.view',
                'employees.view', 'employees.manage', 'employees.viewAny',
                'cards.view', 'cards.manage',
                'id-cards.viewAny', 'id-cards.view', 'id-cards.submitRequest',
                'id-cards.verifyRequest', 'id-cards.printAnytime', 'id-cards.exportPng',
                'id-cards.previewSvg',
                'entitlements.view', 'entitlements.viewAny',
                'service-types.viewAny', 'service-types.view',
                'entitlement-rules.viewAny', 'entitlement-rules.view',
                'occupations.viewAny', 'occupations.view', 'occupations.create', 'occupations.update',
                'isic-activities.viewAny', 'isic-activities.view',
                'positions.viewAny', 'positions.view', 'positions.create', 'positions.update',
                'transfers.viewAny', 'transfers.view', 'transfers.create', 'transfers.update',
                'transfers.submit',
            ],
            'ID Card Officer' => [
                'dashboard.view',
                'cards.view', 'cards.manage',
                'id-cards.viewAny', 'id-cards.view', 'id-cards.create', 'id-cards.update',
                'service-types.viewAny', 'service-types.view',
                'id-cards.submitRequest', 'id-cards.verifyRequest', 'id-cards.approveRequest',
                'id-cards.rejectRequest', 'id-cards.createPrintBatch', 'id-cards.print',
                'id-cards.issue', 'id-cards.activate', 'id-cards.reportLost',
                'id-cards.reportDamaged', 'id-cards.replace', 'id-cards.revoke',
                'card-verifications.viewAny',
                'id-cards.export', 'id-cards.printAnytime', 'id-cards.exportPng',
                'id-cards.previewSvg',
            ],
            'Service Provider User' => [
                'dashboard.view', 'transactions.manage', 'service-transactions.viewAny',
                'providers.viewAny', 'service-types.viewAny', 'service-types.view',
            ],
            'Settlement Officer' => [
                'dashboard.view', 'transactions.view', 'service-transactions.viewAny',
                'providers.viewAny', 'reports.view',
            ],
            'Auditor' => [
                'dashboard.view', 'audit.view', 'audit-logs.viewAny', 'reports.view',
                'occupations.viewAny', 'occupations.view', 'positions.viewAny', 'positions.view',
                'transfers.viewAny', 'transfers.view', 'card-verifications.viewAny',
                'service-types.viewAny', 'entitlement-rules.viewAny',
                'hierarchy-versions.viewAny', 'hierarchy-versions.view',
                'organization-edges.view',
                'code-rules.viewAny', 'code-rules.view', 'code-rules.preview',
            ],
            'Report Viewer' => ['dashboard.view', 'reports.view', 'dashboard.reports'],

            'Cafeteria Admin' => [
                'dashboard.view',
                // Provider Users — manage portal credential accounts
                'cafeteria-provider-users.viewAny', 'cafeteria-provider-users.view',
                'cafeteria-provider-users.create', 'cafeteria-provider-users.update',
                'cafeteria-provider-users.resetPassword', 'cafeteria-provider-users.suspend',
                'cafeteria-provider-users.activate', 'cafeteria-provider-users.delete',
                'cafeteria-provider-users.restore',
                // Providers — full management
                'cafeteria_providers.viewAny', 'cafeteria_providers.view',
                'cafeteria_providers.create', 'cafeteria_providers.update',
                'cafeteria_providers.archive', 'cafeteria_providers.restore',
                // Settings — full management
                'cafeteria_settings.view', 'cafeteria_settings.update',
                'cafeteria_day_rules.view', 'cafeteria_day_rules.update',
                'cafeteria_holidays.create',
                'cafeteria_subsidy_rules.view', 'cafeteria_subsidy_rules.create',
                'cafeteria_subsidy_rules.update', 'cafeteria_subsidy_rules.archive',
                'cafeteria_special_days.view', 'cafeteria_special_days.create',
                'cafeteria_special_days.update', 'cafeteria_special_days.archive',
                // Transactions
                'cafeteria_transactions.view', 'cafeteria_transactions.reverse',
                // Reports & Ledger
                'cafeteria_reports.view', 'cafeteria_reports.generate', 'cafeteria_reports.export',
                'cafeteria_ledger.view',
                // Employee exclusions
                'cafeteria_employee_exclusions.view', 'cafeteria_employee_exclusions.create',
                'cafeteria_employee_exclusions.update', 'cafeteria_employee_exclusions.end',
                'cafeteria_employee_exclusions.archive',
            ],

            'Cafeteria Operator' => [
                'dashboard.view',
                // Read-only on providers
                'cafeteria_providers.viewAny', 'cafeteria_providers.view',
                // Settings — view only
                'cafeteria_settings.view',
                'cafeteria_day_rules.view',
                'cafeteria_subsidy_rules.view',
                'cafeteria_special_days.view',
                // Transactions — view and scan
                'cafeteria_transactions.view', 'cafeteria_transactions.scan',
                // Reports — view only
                'cafeteria_reports.view',
                'cafeteria_ledger.view',
                // Employee exclusions — view only
                'cafeteria_employee_exclusions.view',
            ],

            'Cafeteria Provider' => [
                'cafeteria-portal.login',
                'cafeteria-portal.viewDashboard',
                'cafeteria-portal.scan',
                'cafeteria-portal.viewTransactions',
                'cafeteria-portal.viewLedger',
                'cafeteria-portal.manageMenus',
                'cafeteria-portal.manageOrders',
                'cafeteria-portal.viewReports',
                'cafeteria-portal.updateProfile',
                'cafeteria-portal.exportTransactions',
                'provider-cafeteria-transactions.export',
                'provider-cafeteria-payment-claims.export',
            ],
        ];

        foreach ($roleMap as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }

        $this->command->info('Roles seeded successfully.');
    }
}
