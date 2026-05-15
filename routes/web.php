<?php

declare(strict_types=1);

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\AuditLogController;
use App\Http\Controllers\Web\CardPrintBatchController;
use App\Http\Controllers\Web\CardRequestController;
use App\Http\Controllers\Web\CodeRuleController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\EmployeeTransferController;
use App\Http\Controllers\Web\EntitlementController;
use App\Http\Controllers\Web\EntitlementRuleController;
use App\Http\Controllers\Web\HierarchyVersionController;
use App\Http\Controllers\Web\IdCardController;
use App\Http\Controllers\Web\IsicActivityController;
use App\Http\Controllers\Web\OccupationController;
use App\Http\Controllers\Web\OrganizationController;
use App\Http\Controllers\Web\OrganizationEdgeController;
use App\Http\Controllers\Web\OrganizationTypeController;
use App\Http\Controllers\Web\OrganizationUnitController;
use App\Http\Controllers\Web\OrganizationUnitTypeController;
use App\Http\Controllers\Web\PermissionController;
use App\Http\Controllers\Web\PositionController;
use App\Http\Controllers\Web\RecycleBinController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\ServiceProviderController;
use App\Http\Controllers\Web\ServiceTypeController;
use App\Http\Controllers\Web\SystemSettingController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\UserOrganizationScopeController;
use App\Models\IdCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Organizations
    Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::get('/organizations/parent-options', [OrganizationController::class, 'parentOptions'])->name('organizations.parent-options');
    Route::get('/organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');
    Route::get('/organizations/{organization}/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
    Route::patch('/organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
    Route::delete('/organizations/{organization}', [OrganizationController::class, 'archive'])->name('organizations.archive');

    // Organization Types
    Route::get('/organization-types', [OrganizationTypeController::class, 'index'])->name('organization-types.index');
    Route::get('/organization-types/create', [OrganizationTypeController::class, 'create'])->name('organization-types.create');
    Route::post('/organization-types', [OrganizationTypeController::class, 'store'])->name('organization-types.store');
    Route::get('/organization-types/{organizationType}', [OrganizationTypeController::class, 'show'])->name('organization-types.show');
    Route::get('/organization-types/{organizationType}/edit', [OrganizationTypeController::class, 'edit'])->name('organization-types.edit');
    Route::patch('/organization-types/{organizationType}', [OrganizationTypeController::class, 'update'])->name('organization-types.update');
    Route::delete('/organization-types/{organizationType}', [OrganizationTypeController::class, 'destroy'])->name('organization-types.destroy');
    Route::post('/organization-types/{organizationType}/restore', [OrganizationTypeController::class, 'restore'])->name('organization-types.restore');

    // Hierarchy Versions
    Route::get('/hierarchy-versions', [HierarchyVersionController::class, 'index'])->name('hierarchy-versions.index');
    Route::get('/hierarchy-versions/create', [HierarchyVersionController::class, 'create'])->name('hierarchy-versions.create');
    Route::post('/hierarchy-versions', [HierarchyVersionController::class, 'store'])->name('hierarchy-versions.store');
    Route::get('/hierarchy-versions/{hierarchyVersion}', [HierarchyVersionController::class, 'show'])->name('hierarchy-versions.show');
    Route::get('/hierarchy-versions/{hierarchyVersion}/edit', [HierarchyVersionController::class, 'edit'])->name('hierarchy-versions.edit');
    Route::match(['put', 'patch'], '/hierarchy-versions/{hierarchyVersion}', [HierarchyVersionController::class, 'update'])->name('hierarchy-versions.update');
    Route::post('/hierarchy-versions/{hierarchyVersion}/publish', [HierarchyVersionController::class, 'publish'])->name('hierarchy-versions.publish');
    Route::post('/hierarchy-versions/{hierarchyVersion}/archive', [HierarchyVersionController::class, 'archive'])->name('hierarchy-versions.archive');
    Route::get('/hierarchy-versions/{hierarchyVersion}/tree', [HierarchyVersionController::class, 'tree'])->name('hierarchy-versions.tree');
    Route::get('/hierarchy-versions/{hierarchyVersion}/tree/edit', [HierarchyVersionController::class, 'editTree'])->name('hierarchy-versions.tree.edit');
    Route::get('/hierarchy-versions/{hierarchyVersion}/organization-options', [HierarchyVersionController::class, 'organizationOptions'])->name('hierarchy-versions.organization-options');
    Route::post('/hierarchy-versions/{hierarchyVersion}/edges', [OrganizationEdgeController::class, 'store'])->name('hierarchy-versions.edges.store');
    Route::patch('/hierarchy-versions/{hierarchyVersion}/edges/{organizationEdge}', [OrganizationEdgeController::class, 'update'])->name('hierarchy-versions.edges.update');
    Route::delete('/hierarchy-versions/{hierarchyVersion}/edges/{organizationEdge}', [OrganizationEdgeController::class, 'destroy'])->name('hierarchy-versions.edges.destroy');

    // Organization Units
    Route::get('/organization-units', [OrganizationUnitController::class, 'index'])->name('organization-units.index');
    Route::get('/organization-units/create', [OrganizationUnitController::class, 'create'])->name('organization-units.create');
    Route::post('/organization-units', [OrganizationUnitController::class, 'store'])->name('organization-units.store');
    Route::get('/organization-units/{organizationUnit}', [OrganizationUnitController::class, 'show'])->name('organization-units.show');
    Route::get('/organization-units/{organizationUnit}/edit', [OrganizationUnitController::class, 'edit'])->name('organization-units.edit');
    Route::patch('/organization-units/{organizationUnit}', [OrganizationUnitController::class, 'update'])->name('organization-units.update');
    Route::post('/organization-units/{organizationUnit}/archive', [OrganizationUnitController::class, 'archive'])->name('organization-units.archive');
    Route::post('/organization-units/{organizationUnit}/restore', [OrganizationUnitController::class, 'restore'])->name('organization-units.restore');
    Route::get('/organizations/{organization}/units/options', [OrganizationUnitController::class, 'options'])->name('organizations.units.options');
    Route::get('/organizations/{organization}/units/tree', [OrganizationUnitController::class, 'tree'])->name('organizations.units.tree');

    // Organization Unit Types
    Route::get('/organization-unit-types', [OrganizationUnitTypeController::class, 'index'])->name('organization-unit-types.index');
    Route::get('/organization-unit-types/create', [OrganizationUnitTypeController::class, 'create'])->name('organization-unit-types.create');
    Route::post('/organization-unit-types', [OrganizationUnitTypeController::class, 'store'])->name('organization-unit-types.store');
    Route::get('/organization-unit-types/{organizationUnitType}', [OrganizationUnitTypeController::class, 'show'])->name('organization-unit-types.show');
    Route::get('/organization-unit-types/{organizationUnitType}/edit', [OrganizationUnitTypeController::class, 'edit'])->name('organization-unit-types.edit');
    Route::patch('/organization-unit-types/{organizationUnitType}', [OrganizationUnitTypeController::class, 'update'])->name('organization-unit-types.update');
    Route::post('/organization-unit-types/{organizationUnitType}/archive', [OrganizationUnitTypeController::class, 'archive'])->name('organization-unit-types.archive');
    Route::post('/organization-unit-types/{organizationUnitType}/restore', [OrganizationUnitTypeController::class, 'restore'])->name('organization-unit-types.restore');
    Route::get('/api/organization-unit-types/options', [OrganizationUnitTypeController::class, 'options'])->name('organization-unit-types.options');

    // Employees
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::patch('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::post('/employees/{employee}/transfers', [EmployeeController::class, 'transfer'])->name('employees.transfer');

    // Occupations
    Route::get('/occupations', [OccupationController::class, 'index'])->name('occupations.index');
    Route::get('/occupations/create', [OccupationController::class, 'create'])->name('occupations.create');
    Route::post('/occupations', [OccupationController::class, 'store'])->name('occupations.store');
    Route::get('/occupations/{occupation}', [OccupationController::class, 'show'])->name('occupations.show');
    Route::get('/occupations/{occupation}/edit', [OccupationController::class, 'edit'])->name('occupations.edit');
    Route::patch('/occupations/{occupation}', [OccupationController::class, 'update'])->name('occupations.update');
    Route::delete('/occupations/{occupation}', [OccupationController::class, 'archive'])->name('occupations.archive');
    Route::post('/occupations/{occupation}/restore', [OccupationController::class, 'restore'])->name('occupations.restore');

    // ISIC Activities
    Route::get('/isic-activities', [IsicActivityController::class, 'index'])->name('isic-activities.index');
    Route::get('/isic-activities/create', [IsicActivityController::class, 'create'])->name('isic-activities.create');
    Route::post('/isic-activities', [IsicActivityController::class, 'store'])->name('isic-activities.store');
    Route::get('/isic-activities/{isicActivity}', [IsicActivityController::class, 'show'])->name('isic-activities.show');
    Route::get('/isic-activities/{isicActivity}/edit', [IsicActivityController::class, 'edit'])->name('isic-activities.edit');
    Route::patch('/isic-activities/{isicActivity}', [IsicActivityController::class, 'update'])->name('isic-activities.update');
    Route::delete('/isic-activities/{isicActivity}', [IsicActivityController::class, 'archive'])->name('isic-activities.archive');
    Route::post('/isic-activities/{isicActivity}/restore', [IsicActivityController::class, 'restore'])->name('isic-activities.restore');

    // Positions
    Route::get('/positions', [PositionController::class, 'index'])->name('positions.index');
    Route::get('/positions/create', [PositionController::class, 'create'])->name('positions.create');
    Route::post('/positions', [PositionController::class, 'store'])->name('positions.store');
    Route::get('/positions/{position}', [PositionController::class, 'show'])->name('positions.show');
    Route::get('/positions/{position}/edit', [PositionController::class, 'edit'])->name('positions.edit');
    Route::patch('/positions/{position}', [PositionController::class, 'update'])->name('positions.update');
    Route::delete('/positions/{position}', [PositionController::class, 'archive'])->name('positions.archive');
    Route::post('/positions/{position}/restore', [PositionController::class, 'restore'])->name('positions.restore');

    // Employee Transfers
    Route::get('/employee-transfers', [EmployeeTransferController::class, 'index'])->name('employee-transfers.index');
    Route::get('/employee-transfers/pending', [EmployeeTransferController::class, 'pending'])->name('employee-transfers.pending');
    Route::get('/employee-transfers/create', [EmployeeTransferController::class, 'create'])->name('employee-transfers.create');
    Route::post('/employee-transfers', [EmployeeTransferController::class, 'store'])->name('employee-transfers.store');
    Route::get('/employee-transfers/{employeeTransfer}', [EmployeeTransferController::class, 'show'])->name('employee-transfers.show');
    Route::get('/employee-transfers/{employeeTransfer}/edit', [EmployeeTransferController::class, 'edit'])->name('employee-transfers.edit');
    Route::patch('/employee-transfers/{employeeTransfer}', [EmployeeTransferController::class, 'update'])->name('employee-transfers.update');
    Route::post('/employee-transfers/{employeeTransfer}/submit', [EmployeeTransferController::class, 'submit'])->name('employee-transfers.submit');
    Route::post('/employee-transfers/{employeeTransfer}/confirm-current-organization', [EmployeeTransferController::class, 'confirmCurrentOrganization'])->name('employee-transfers.confirm-current-organization');
    Route::post('/employee-transfers/{employeeTransfer}/confirm-receiving-organization', [EmployeeTransferController::class, 'confirmReceivingOrganization'])->name('employee-transfers.confirm-receiving-organization');
    Route::post('/employee-transfers/{employeeTransfer}/approve', [EmployeeTransferController::class, 'approve'])->name('employee-transfers.approve');
    Route::post('/employee-transfers/{employeeTransfer}/reject', [EmployeeTransferController::class, 'reject'])->name('employee-transfers.reject');
    Route::post('/employee-transfers/{employeeTransfer}/cancel', [EmployeeTransferController::class, 'cancel'])->name('employee-transfers.cancel');
    Route::post('/employee-transfers/{employeeTransfer}/complete', [EmployeeTransferController::class, 'complete'])->name('employee-transfers.complete');

    // ID Cards
    Route::get('/id-cards', [IdCardController::class, 'index'])->name('id-cards.index');
    Route::get('/id-cards/{card}', [IdCardController::class, 'show'])->name('id-cards.show');
    Route::get('/id-cards/{card}/preview', [IdCardController::class, 'preview'])->name('id-cards.preview');
    Route::post('/id-cards/{card}/issue', [IdCardController::class, 'issue'])->name('id-cards.issue');
    Route::post('/id-cards/{card}/activate', [IdCardController::class, 'activate'])->name('id-cards.activate');
    Route::post('/id-cards/{card}/report-lost', [IdCardController::class, 'reportLost'])->name('id-cards.report-lost');
    Route::post('/id-cards/{card}/report-damaged', [IdCardController::class, 'reportDamaged'])->name('id-cards.report-damaged');
    Route::post('/id-cards/{card}/replace', [IdCardController::class, 'replace'])->name('id-cards.replace');
    Route::post('/id-cards/{card}/revoke', [IdCardController::class, 'revoke'])->name('id-cards.revoke');
    Route::post('/id-cards/{card}/export/audit', [IdCardController::class, 'exportAudit'])->name('id-cards.export.audit');

    // Card Requests
    Route::get('/card-requests', [CardRequestController::class, 'index'])->name('card-requests.index');
    Route::get('/card-requests/create', [CardRequestController::class, 'create'])->name('card-requests.create');
    Route::post('/card-requests', [CardRequestController::class, 'store'])->name('card-requests.store');
    Route::get('/card-requests/{cardRequest}', [CardRequestController::class, 'show'])->name('card-requests.show');
    Route::post('/card-requests/{cardRequest}/verify', [CardRequestController::class, 'verify'])->name('card-requests.verify');
    Route::post('/card-requests/{cardRequest}/approve', [CardRequestController::class, 'approve'])->name('card-requests.approve');
    Route::post('/card-requests/{cardRequest}/reject', [CardRequestController::class, 'reject'])->name('card-requests.reject');
    Route::post('/card-requests/{cardRequest}/cancel', [CardRequestController::class, 'cancel'])->name('card-requests.cancel');

    // Print Batches
    Route::get('/print-batches', [CardPrintBatchController::class, 'index'])->name('print-batches.index');
    Route::get('/print-batches/create', [CardPrintBatchController::class, 'create'])->name('print-batches.create');
    Route::post('/print-batches', [CardPrintBatchController::class, 'store'])->name('print-batches.store');
    Route::get('/print-batches/{batch}', [CardPrintBatchController::class, 'show'])->name('print-batches.show');
    Route::post('/print-batches/{batch}/mark-printed', [CardPrintBatchController::class, 'markPrinted'])->name('print-batches.mark-printed');

    // Legacy ID Card routes (kept for backward compat with existing frontend)
    Route::post('/id-cards/requests', [CardRequestController::class, 'store'])->name('id-cards.requests.store');
    Route::post('/id-cards/requests/{cardRequest}/verify', [CardRequestController::class, 'verify'])->name('id-cards.requests.verify');
    Route::post('/id-cards/requests/{cardRequest}/approve', [CardRequestController::class, 'approve'])->name('id-cards.requests.approve');
    // Legacy incident route
    Route::post('/id-cards/{card}/incident', function (Request $request, IdCard $card) {
        $status = $request->input('status', 'lost');

        return redirect()->route($status === 'damaged' ? 'id-cards.report-damaged' : 'id-cards.report-lost', $card);
    })->name('id-cards.incident');

    // Service Providers & Entitlements
    Route::get('/service-types', [ServiceTypeController::class, 'index'])->name('service-types.index');
    Route::get('/service-types/create', [ServiceTypeController::class, 'create'])->name('service-types.create');
    Route::post('/service-types', [ServiceTypeController::class, 'store'])->name('service-types.store');
    Route::get('/service-types/{serviceType}', [ServiceTypeController::class, 'show'])->name('service-types.show');
    Route::get('/service-types/{serviceType}/edit', [ServiceTypeController::class, 'edit'])->name('service-types.edit');
    Route::patch('/service-types/{serviceType}', [ServiceTypeController::class, 'update'])->name('service-types.update');
    Route::delete('/service-types/{serviceType}', [ServiceTypeController::class, 'archive'])->name('service-types.archive');
    Route::post('/service-types/{serviceType}/restore', [ServiceTypeController::class, 'restore'])->name('service-types.restore');

    Route::get('/service-providers', [ServiceProviderController::class, 'index'])->name('service-providers.index');
    Route::get('/service-providers/{serviceProvider}', [ServiceProviderController::class, 'show'])->name('service-providers.show');
    Route::get('/entitlement-rules', [EntitlementRuleController::class, 'index'])->name('entitlement-rules.index');
    Route::get('/entitlement-rules/create', [EntitlementRuleController::class, 'create'])->name('entitlement-rules.create');
    Route::post('/entitlement-rules', [EntitlementRuleController::class, 'store'])->name('entitlement-rules.store');
    Route::get('/entitlement-rules/{entitlementRule}', [EntitlementRuleController::class, 'show'])->name('entitlement-rules.show');
    Route::get('/entitlement-rules/{entitlementRule}/edit', [EntitlementRuleController::class, 'edit'])->name('entitlement-rules.edit');
    Route::patch('/entitlement-rules/{entitlementRule}', [EntitlementRuleController::class, 'update'])->name('entitlement-rules.update');
    Route::delete('/entitlement-rules/{entitlementRule}', [EntitlementRuleController::class, 'archive'])->name('entitlement-rules.archive');
    Route::post('/entitlement-rules/{entitlementRule}/restore', [EntitlementRuleController::class, 'restore'])->name('entitlement-rules.restore');
    Route::get('/entitlements', [EntitlementController::class, 'index'])->name('entitlements.index');
    Route::post('/entitlements', [EntitlementController::class, 'store'])->name('entitlements.store');

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Administration: Recycle Bin
    Route::get('/recycle-bin', [RecycleBinController::class, 'index'])->name('recycle-bin.index');
    Route::post('/recycle-bin/{type}/{id}/restore', [RecycleBinController::class, 'restore'])->name('recycle-bin.restore');

    // Administration: Code Rules
    Route::get('/code-rules', [CodeRuleController::class, 'index'])->name('code-rules.index');
    Route::get('/code-rules/create', [CodeRuleController::class, 'create'])->name('code-rules.create');
    Route::post('/code-rules', [CodeRuleController::class, 'store'])->name('code-rules.store');
    Route::post('/code-rules/preview', [CodeRuleController::class, 'preview'])->name('code-rules.preview');
    Route::post('/code-rules/preview-code', [CodeRuleController::class, 'previewCode'])->name('code-rules.preview-code');
    Route::get('/code-rules/{codeRule}', [CodeRuleController::class, 'show'])->name('code-rules.show');
    Route::get('/code-rules/{codeRule}/edit', [CodeRuleController::class, 'edit'])->name('code-rules.edit');
    Route::match(['put', 'patch'], '/code-rules/{codeRule}', [CodeRuleController::class, 'update'])->name('code-rules.update');
    Route::post('/code-rules/{codeRule}/archive', [CodeRuleController::class, 'archive'])->name('code-rules.archive');
    Route::post('/code-rules/{codeRule}/restore', [CodeRuleController::class, 'restore'])->name('code-rules.restore');
    Route::get('/code-rules/{codeRule}/sequences', [CodeRuleController::class, 'sequences'])->name('code-rules.sequences');
    Route::post('/code-rules/{codeRule}/sequences/{sequence}/reset', [CodeRuleController::class, 'resetSequence'])->name('code-rules.sequences.reset');

    // Admin: Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('/users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::post('/users/{user}/assign-roles', [UserController::class, 'assignRoles'])->name('users.assign-roles');

    Route::prefix('users/{user}')->group(function (): void {
        Route::get('/organization-scopes', [UserOrganizationScopeController::class, 'index'])->name('users.organization-scopes.index');
        Route::post('/organization-scopes', [UserOrganizationScopeController::class, 'store'])->name('users.organization-scopes.store');
        Route::put('/organization-scopes/{scope}', [UserOrganizationScopeController::class, 'update'])->name('users.organization-scopes.update');
        Route::delete('/organization-scopes/{scope}', [UserOrganizationScopeController::class, 'destroy'])->name('users.organization-scopes.destroy');
    });

    // Admin: Roles
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::patch('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // Admin: Permissions
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::get('/permissions/{permission}', [PermissionController::class, 'show'])->name('permissions.show');
    Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
    Route::patch('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

    // Admin: System Settings
    Route::get('/system-settings', [SystemSettingController::class, 'index'])->name('system-settings.index');
    Route::patch('/system-settings/general', [SystemSettingController::class, 'updateGeneral'])->name('system-settings.general.update');
    Route::patch('/system-settings/localization', [SystemSettingController::class, 'updateLocalization'])->name('system-settings.localization.update');
    Route::patch('/system-settings/notifications', [SystemSettingController::class, 'updateNotifications'])->name('system-settings.notifications.update');
    Route::patch('/system-settings/email', [SystemSettingController::class, 'updateEmail'])->name('system-settings.email.update');
    Route::patch('/system-settings/sms', [SystemSettingController::class, 'updateSms'])->name('system-settings.sms.update');
    Route::patch('/system-settings/telegram', [SystemSettingController::class, 'updateTelegram'])->name('system-settings.telegram.update');
    Route::patch('/system-settings/security', [SystemSettingController::class, 'updateSecurity'])->name('system-settings.security.update');
    Route::patch('/system-settings/appearance', [SystemSettingController::class, 'updateAppearance'])->name('system-settings.appearance.update');
    Route::patch('/system-settings/id-cards', [SystemSettingController::class, 'updateIdCards'])->name('system-settings.id-cards.update');
    Route::post('/system-settings/test-email', [SystemSettingController::class, 'testEmail'])->name('system-settings.test-email');
    Route::post('/system-settings/test-sms', [SystemSettingController::class, 'testSms'])->name('system-settings.test-sms');
    Route::post('/system-settings/test-telegram', [SystemSettingController::class, 'testTelegram'])->name('system-settings.test-telegram');
    Route::post('/system-settings/clear-cache', [SystemSettingController::class, 'clearCache'])->name('system-settings.clear-cache');
    Route::patch('/system-settings/{setting}', [SystemSettingController::class, 'update'])->name('system-settings.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
