<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Manufacturing\Controllers\BomController;
use App\Modules\Manufacturing\Controllers\BomCrudController;
use App\Modules\Manufacturing\Controllers\CostingController;
use App\Modules\Manufacturing\Controllers\QualityController;
use App\Modules\Manufacturing\Controllers\ScrapController;

Route::middleware(['auth:jwt', 'permission:manufacturing'])->prefix('manufacturing')->group(function () {
    Route::get('boms', [BomController::class, 'index']);

    Route::get('boms-crud', [BomCrudController::class, 'index']);
    Route::post('boms-crud', [BomCrudController::class, 'store']);
    Route::get('boms-crud/{id}', [BomCrudController::class, 'show']);
    Route::put('boms-crud/{id}', [BomCrudController::class, 'update']);
    Route::patch('boms-crud/{id}', [BomCrudController::class, 'update']);

    Route::post('costing/calculate-work-order/{woId}', [CostingController::class, 'calculateWorkOrder']);
    Route::get('costing/work-order/{woId}', [CostingController::class, 'workOrderCost']);
    Route::get('costing/variance-analysis', [CostingController::class, 'varianceAnalysis']);
    Route::get('costing/cost-trends', [CostingController::class, 'costTrends']);

    Route::get('quality/inspections', [QualityController::class, 'inspections']);
    Route::post('quality/inspections', [QualityController::class, 'createInspection']);
    Route::put('quality/inspections/{id}/record-readings', [QualityController::class, 'recordReadings']);
    Route::post('quality/inspections/{id}/complete', [QualityController::class, 'completeInspection']);

    Route::get('scrap', [ScrapController::class, 'index']);
    Route::post('scrap', [ScrapController::class, 'store']);
    Route::post('scrap/{id}/dispose', [ScrapController::class, 'dispose']);
    Route::post('scrap/{id}/recover', [ScrapController::class, 'recover']);
    Route::get('scrap/analysis', [ScrapController::class, 'analysis']);
    Route::get('scrap/trends', [ScrapController::class, 'trends']);
});
