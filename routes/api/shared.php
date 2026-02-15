<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Shared\Controllers\ApprovalWorkflowController;
use App\Modules\Shared\Controllers\DashboardController;
use App\Modules\Shared\Controllers\NumberSeriesController;
use App\Modules\Shared\Controllers\SystemSettingController;

Route::middleware(['auth:jwt', 'permission:shared'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'show']);
});

Route::middleware(['auth:jwt', 'permission:shared'])->prefix('shared')->group(function () {
    Route::apiResource('number-series', NumberSeriesController::class);
    Route::apiResource('system-settings', SystemSettingController::class);

    Route::get('approval-workflows', [ApprovalWorkflowController::class, 'workflows']);
    Route::post('approval-workflows', [ApprovalWorkflowController::class, 'storeWorkflow']);
    Route::post('approval-requests', [ApprovalWorkflowController::class, 'requestApproval']);
    Route::get('approval-requests/pending', [ApprovalWorkflowController::class, 'pending']);
    Route::post('approval-requests/{id}/approve', [ApprovalWorkflowController::class, 'approve']);
    Route::post('approval-requests/{id}/reject', [ApprovalWorkflowController::class, 'reject']);
});
