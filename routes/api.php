<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\CardVerificationController;
use App\Http\Controllers\Api\V1\EmployeeEntitlementController;
use App\Http\Controllers\Api\V1\OfflineSyncController;
use App\Http\Controllers\Api\V1\ProviderSettlementController;
use App\Http\Controllers\Api\V1\ServiceAuthorizationController;
use App\Http\Controllers\Api\V1\ServiceTransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api', 'provider.scope'])->prefix('v1')->group(function (): void {
    Route::post('/cards/verify', CardVerificationController::class)->name('api.v1.cards.verify');
    Route::post('/services/{serviceType}/authorize', ServiceAuthorizationController::class)->name('api.v1.services.authorize');
    Route::post('/services/{serviceType}/transactions', ServiceTransactionController::class)->name('api.v1.services.transactions');
    Route::get('/employees/{employee}/entitlements', EmployeeEntitlementController::class)->name('api.v1.employees.entitlements');
    Route::get('/providers/{provider}/settlements/{period}', ProviderSettlementController::class)->name('api.v1.providers.settlements');
    Route::post('/offline-sync/transactions', OfflineSyncController::class)->name('api.v1.offline-sync.transactions');
});
