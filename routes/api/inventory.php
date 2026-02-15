<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Inventory\Controllers\ItemController;
use App\Modules\Inventory\Controllers\StockAdjustmentController;

Route::middleware(['auth:jwt', 'permission:inventory'])->prefix('inventory')->group(function () {
    Route::apiResource('items', ItemController::class);
    Route::get('items-active', [ItemController::class, 'active']);
    Route::get('items/{id}/stock-levels', [ItemController::class, 'stockLevels']);
    Route::get('items/{id}/transaction-history', [ItemController::class, 'transactionHistory']);

    Route::get('stock-adjustments', [StockAdjustmentController::class, 'index']);
    Route::post('stock-adjustments', [StockAdjustmentController::class, 'store']);
    Route::get('stock-adjustments/pending-approval', [StockAdjustmentController::class, 'pendingApproval']);
    Route::get('stock-adjustments/{id}', [StockAdjustmentController::class, 'show']);
    Route::post('stock-adjustments/{id}/submit', [StockAdjustmentController::class, 'submit']);
    Route::post('stock-adjustments/{id}/approve', [StockAdjustmentController::class, 'approve']);
    Route::post('stock-adjustments/{id}/post', [StockAdjustmentController::class, 'post']);
});
