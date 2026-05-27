<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = require database_path('seeders/data/permissions.php');
        foreach ($catalog as $entry) {
            Permission::updateOrCreate(
                ['name' => $entry['name'], 'guard_name' => 'web'],
                array_diff_key($entry, ['name' => 1]),
            );
        }

        $legacy = [
            'dashboard.view', 'dashboard.reports',
            'organizations.view', 'organizations.manage',
            'employees.view', 'employees.manage',
            'cards.view', 'cards.manage',
            'entitlements.view', 'entitlements.manage',
            'transactions.view', 'transactions.manage',
            'audit.view', 'reports.view', 'reports.export',
            'organization-types.viewAny', 'organization-types.view', 'organization-types.create',
            'organization-types.update', 'organization-types.delete', 'organization-types.restore',
            'organization-types.viewDeleted',
            'organizations.viewAny',
            'hierarchy-versions.viewAny', 'hierarchy-versions.view', 'hierarchy-versions.create',
            'hierarchy-versions.update', 'hierarchy-versions.archive', 'hierarchy-versions.publish',
            'hierarchy-versions.manageTree',
            'organization-edges.view', 'organization-edges.create', 'organization-edges.update',
            'organization-edges.remove',
            'users.viewAny', 'users.view', 'users.create', 'users.update', 'users.delete',
            'users.archive', 'users.restore', 'users.assignRoles', 'users.resetPassword',
            'users.deactivate', 'users.updateProfilePhoto', 'users.viewSensitive',
            'users.assignOrganizationScopes',
            'user-organization-scopes.viewAny', 'user-organization-scopes.create',
            'user-organization-scopes.update', 'user-organization-scopes.delete',
            'user-organization-scopes.restore',
            'roles.viewAny', 'roles.view', 'roles.create', 'roles.update', 'roles.delete',
            'roles.assignPermissions',
            'permissions.viewAny', 'permissions.view',
            'system-settings.view', 'system-settings.update', 'system-settings.manageUi',
            'system-settings.manageGeneral', 'system-settings.manageLocalization',
            'system-settings.manageNotifications', 'system-settings.manageEmail',
            'system-settings.manageSms', 'system-settings.manageTelegram',
            'system-settings.manageSecurity', 'system-settings.manageAppearance',
            'system-settings.manageIdCards', 'system-settings.clearCache',
            'system-settings.testNotificationChannels', 'system-settings.uploadAssets',
            'id-cards.viewAny', 'id-cards.view', 'id-cards.create', 'id-cards.update',
            'id-cards.archive', 'id-cards.submitRequest', 'id-cards.verifyRequest',
            'id-cards.approveRequest', 'id-cards.rejectRequest', 'id-cards.createPrintBatch',
            'id-cards.print', 'id-cards.issue', 'id-cards.activate', 'id-cards.reportLost',
            'id-cards.reportDamaged', 'id-cards.replace', 'id-cards.revoke', 'id-cards.verify',
            'id-cards.export', 'id-cards.printAnytime', 'id-cards.exportPng', 'id-cards.previewSvg',
            'card-verifications.viewAny',
            'employees.viewAny', 'employees.viewPii', 'entitlements.viewAny', 'providers.viewAny',
            'service-transactions.viewAny', 'audit-logs.viewAny',
            'occupations.viewAny', 'occupations.view', 'occupations.create', 'occupations.update',
            'occupations.archive', 'occupations.delete', 'occupations.restore', 'occupations.export',
            'isic-activities.viewAny', 'isic-activities.view', 'isic-activities.create',
            'isic-activities.update', 'isic-activities.archive', 'isic-activities.delete',
            'isic-activities.restore', 'isic-activities.export',
            'positions.viewAny', 'positions.view', 'positions.create', 'positions.update',
            'positions.archive', 'positions.delete', 'positions.restore', 'positions.viewDeleted',
            'positions.export',
            'transfers.viewAny', 'transfers.view', 'transfers.create', 'transfers.update',
            'transfers.submit', 'transfers.confirmCurrentOrganization',
            'transfers.confirmReceivingOrganization', 'transfers.approve', 'transfers.reject',
            'transfers.cancel', 'transfers.complete', 'transfers.export',
            'service-types.viewAny', 'service-types.view', 'service-types.create',
            'service-types.update', 'service-types.archive', 'service-types.delete',
            'service-types.restore', 'service-types.viewDeleted', 'service-types.export',
            'entitlement-rules.viewAny', 'entitlement-rules.view', 'entitlement-rules.create',
            'entitlement-rules.update', 'entitlement-rules.archive', 'entitlement-rules.delete',
            'entitlement-rules.restore', 'entitlement-rules.viewDeleted', 'entitlement-rules.export',
            'code-rules.viewAny', 'code-rules.view', 'code-rules.create', 'code-rules.update',
            'code-rules.archive', 'code-rules.delete', 'code-rules.restore', 'code-rules.viewDeleted',
            'code-rules.preview', 'code-rules.generate', 'code-rules.export',
            'code-rules.manageOverrides', 'code-rules.viewSequences', 'code-rules.manageSequences',
            'code-rules.resetSequence',
            'organization-units.viewAny', 'organization-units.view', 'organization-units.create',
            'organization-units.update', 'organization-units.archive', 'organization-units.delete',
            'organization-units.restore', 'organization-units.viewDeleted',
            'organization-units.manageHierarchy', 'organization-units.export',
            'organization-unit-types.viewAny', 'organization-unit-types.view',
            'organization-unit-types.create', 'organization-unit-types.update',
            'organization-unit-types.archive', 'organization-unit-types.delete',
            'organization-unit-types.restore', 'organization-unit-types.viewDeleted',
            'recycle-bin.view', 'recycle-bin.restore', 'recycle-bin.viewDetails',
            'recycle-bin.forceDelete',
        ];

        foreach ($legacy as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('Permissions seeded successfully.');
    }
}
