<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Procurement\Controllers\VendorController;
use App\Modules\Procurement\Controllers\PurchaseOrderController;
use App\Modules\Procurement\Controllers\GoodsReceiptNoteController;
use App\Modules\Procurement\Controllers\PurchaseInvoiceController;

Route::middleware(['auth:jwt', 'permission:procurement'])->prefix('procurement')->group(function () {
    Route::apiResource('vendors', VendorController::class);
    Route::apiResource('purchase-orders', PurchaseOrderController::class);
    Route::post('purchase-orders/{id}/submit', [PurchaseOrderController::class, 'submit']);
    Route::post('purchase-orders/{id}/approve', [PurchaseOrderController::class, 'approve']);
    Route::post('purchase-orders/{id}/cancel', [PurchaseOrderController::class, 'destroy']);
    Route::apiResource('grn', GoodsReceiptNoteController::class)->parameters(['grn' => 'id']);
    Route::post('grn/{id}/complete', [GoodsReceiptNoteController::class, 'complete']);
    Route::apiResource('purchase-invoices', PurchaseInvoiceController::class);
});
