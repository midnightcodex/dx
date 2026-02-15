<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Reports\Controllers\ReportDefinitionController;
use App\Modules\Reports\Controllers\ReportExecutionController;

Route::middleware(['auth:jwt', 'permission:reports'])->prefix('reports')->group(function () {
    Route::get('definitions', [ReportDefinitionController::class, 'index']);
    Route::post('definitions', [ReportDefinitionController::class, 'store']);
    Route::get('definitions/{id}', [ReportDefinitionController::class, 'show']);
    Route::put('definitions/{id}', [ReportDefinitionController::class, 'update']);
    Route::delete('definitions/{id}', [ReportDefinitionController::class, 'destroy']);

    Route::get('inventory/stock-summary', [ReportExecutionController::class, 'inventoryStockSummary']);
    Route::get('manufacturing/production-summary', [ReportExecutionController::class, 'manufacturingProductionSummary']);
    Route::get('sales/sales-analysis', [ReportExecutionController::class, 'salesAnalysis']);
    Route::post('custom/execute', [ReportExecutionController::class, 'executeCustom']);
});
