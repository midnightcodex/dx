<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Inventory\Controllers\WarehouseController;
use App\Modules\Inventory\Controllers\UomController;
use App\Modules\Inventory\Controllers\ItemCategoryController;
use App\Modules\Inventory\Controllers\WarehouseCrudController;

Route::middleware(['auth:jwt', 'permission:inventory'])->prefix('inventory')->group(function () {
    Route::get('warehouses', [WarehouseController::class, 'index']);
    Route::get('uoms', [UomController::class, 'index']);
    Route::get('item-categories', [ItemCategoryController::class, 'index']);

    Route::get('warehouses-crud', [WarehouseCrudController::class, 'index']);
    Route::post('warehouses-crud', [WarehouseCrudController::class, 'store']);
    Route::get('warehouses-crud/{id}', [WarehouseCrudController::class, 'show']);
    Route::put('warehouses-crud/{id}', [WarehouseCrudController::class, 'update']);
    Route::patch('warehouses-crud/{id}', [WarehouseCrudController::class, 'update']);
});
