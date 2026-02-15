<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\AuthController;

Route::prefix('auth')->group(function () {
    // Public
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    // Protected
    Route::middleware('auth:jwt')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});


