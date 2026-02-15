<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Integrations\Controllers\AccountingExportController;
use App\Modules\Integrations\Controllers\BarcodeController;
use App\Modules\Integrations\Controllers\WeighbridgeController;
use App\Modules\Integrations\Controllers\ApiConfigurationController;

Route::middleware(['auth:jwt', 'permission:integrations'])->prefix('integrations')->group(function () {
    Route::get('accounting/exports', [AccountingExportController::class, 'index']);
    Route::post('accounting/exports', [AccountingExportController::class, 'store']);
    Route::get('accounting/exports/{id}', [AccountingExportController::class, 'show']);
    Route::get('accounting/exports/{id}/download', [AccountingExportController::class, 'download']);
    Route::post('accounting/export-invoices', [AccountingExportController::class, 'exportInvoices']);
    Route::post('accounting/export-stock-valuation', [AccountingExportController::class, 'exportStockValuation']);

    Route::post('barcode/generate', [BarcodeController::class, 'generate']);
    Route::post('barcode/scan', [BarcodeController::class, 'scan']);
    Route::post('barcode/print-batch', [BarcodeController::class, 'printBatch']);

    Route::apiResource('weighbridge-readings', WeighbridgeController::class)->only(['index', 'store', 'show']);

    Route::apiResource('api-configurations', ApiConfigurationController::class);
});
