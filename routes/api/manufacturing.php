<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Manufacturing\Controllers\WorkOrderController;
use App\Modules\Manufacturing\Controllers\ProductionPlanController;

Route::middleware(['auth:jwt', 'permission:manufacturing'])->prefix('manufacturing')->group(function () {
    Route::get('work-orders', [WorkOrderController::class, 'index']);
    Route::post('work-orders', [WorkOrderController::class, 'store']);
    Route::get('work-orders/{id}', [WorkOrderController::class, 'show']);
    Route::put('work-orders/{id}', [WorkOrderController::class, 'update']);
    Route::patch('work-orders/{id}', [WorkOrderController::class, 'update']);
    Route::post('work-orders/{id}/release', [WorkOrderController::class, 'release']);
    Route::post('work-orders/{id}/issue-materials', [WorkOrderController::class, 'issueMaterials']);
    Route::post('work-orders/{id}/record-production', [WorkOrderController::class, 'recordProduction']);
    Route::post('work-orders/{id}/complete', [WorkOrderController::class, 'complete']);

    Route::get('production-plans', [ProductionPlanController::class, 'index']);
    Route::post('production-plans', [ProductionPlanController::class, 'store']);
    Route::get('production-plans/{id}', [ProductionPlanController::class, 'show']);
    Route::put('production-plans/{id}', [ProductionPlanController::class, 'update']);
    Route::patch('production-plans/{id}', [ProductionPlanController::class, 'update']);
    Route::delete('production-plans/{id}', [ProductionPlanController::class, 'destroy']);
    Route::post('production-plans/{id}/approve', [ProductionPlanController::class, 'approve']);
    Route::post('production-plans/{id}/generate-work-orders', [ProductionPlanController::class, 'generateWorkOrders']);
    Route::get('production-plans/{id}/capacity-analysis', [ProductionPlanController::class, 'capacityAnalysis']);
    Route::get('production-plans/{id}/material-requirements', [ProductionPlanController::class, 'materialRequirements']);
});
