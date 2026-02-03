<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Inventory\Controllers\ItemController;
use App\Modules\Manufacturing\Controllers\WorkOrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Auth Routes (Public)
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Inventory - Items
    Route::apiResource('inventory/items', ItemController::class);

    // Manufacturing - Work Orders
    Route::get('manufacturing/work-orders', [WorkOrderController::class, 'index']);
    Route::post('manufacturing/work-orders', [WorkOrderController::class, 'store']);
    Route::get('manufacturing/work-orders/{id}', [WorkOrderController::class, 'show']);
    Route::post('manufacturing/work-orders/{id}/release', [WorkOrderController::class, 'release']);
});
