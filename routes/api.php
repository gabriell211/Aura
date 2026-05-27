<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BillingController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\ContractController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\EquipmentController;
use App\Http\Controllers\Api\V1\InfinitePayWebhookController;
use App\Http\Controllers\Api\V1\MeterReadController;
use App\Http\Controllers\Api\V1\PrintwayyWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('billing/infinitepay/webhook', InfinitePayWebhookController::class)
        ->name('api.v1.billing.infinitepay.webhook');

    Route::middleware('tenant')->prefix('auth')->group(function (): void {
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::middleware(['auth:sanctum', 'tenant'])->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('dashboard/summary', [DashboardController::class, 'summary']);

        Route::apiResource('clients', ClientController::class);
        Route::apiResource('contracts', ContractController::class);
        Route::apiResource('equipment', EquipmentController::class);

        Route::post('meter-reads', [MeterReadController::class, 'store']);
        Route::post('printwayy/sync', [PrintwayyWebhookController::class, 'sync']);

        Route::get('invoices', [BillingController::class, 'index']);
        Route::get('invoices/{invoice}', [BillingController::class, 'show']);
        Route::post('contracts/{contract}/invoices/generate', [BillingController::class, 'generate']);
    });

    Route::prefix('printwayy')->middleware(['tenant', 'printwayy.signature'])->group(function (): void {
        Route::post('meter-reads', [PrintwayyWebhookController::class, 'meterRead']);
        Route::post('alerts', [PrintwayyWebhookController::class, 'alert']);
    });
});
