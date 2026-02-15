<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Sales\Controllers\CustomerController;
use App\Modules\Sales\Controllers\SalesOrderController;
use App\Modules\Sales\Controllers\DeliveryNoteController;
use App\Modules\Sales\Controllers\SalesReturnController;

Route::middleware(['auth:jwt', 'permission:sales'])->prefix('sales')->group(function () {
    Route::apiResource('customers', CustomerController::class);
    Route::get('orders/pending-dispatch', [SalesOrderController::class, 'pendingDispatch']);
    Route::apiResource('orders', SalesOrderController::class);
    Route::post('orders/{id}/confirm', [SalesOrderController::class, 'confirm']);
    Route::post('orders/{id}/reserve-stock', [SalesOrderController::class, 'reserveStock']);
    Route::post('orders/{id}/cancel', [SalesOrderController::class, 'destroy']);
    Route::apiResource('delivery-notes', DeliveryNoteController::class);
    Route::post('delivery-notes/{id}/dispatch', [DeliveryNoteController::class, 'dispatch']);
    Route::apiResource('returns', SalesReturnController::class);
});
