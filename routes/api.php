<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API V1 Controllers
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\SalesController;
use App\Http\Controllers\Api\V1\EmployeeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Standard REST API for external integrations (Mobile Apps, Webhooks, 3rd Party).
| All routes are prefixed with /api (automatically) and versioned.
|
*/

// V1 Routes
Route::prefix('v1')->group(function () {

    // Public Auth
    Route::post('/login', [AuthController::class, 'login']);

    // Protected API
    Route::middleware('auth:sanctum')->group(function () {

        // Auth Management
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Inventory
        Route::get('/inventory/items', [InventoryController::class, 'items']);
        Route::get('/inventory/items/{id}', [InventoryController::class, 'itemDetail']);

        // Sales
        Route::get('/sales/orders', [SalesController::class, 'orders']);
        Route::post('/sales/orders', [SalesController::class, 'storeOrder']);

        // HR
        Route::get('/hr/employees', [EmployeeController::class, 'index']);
    });
});
