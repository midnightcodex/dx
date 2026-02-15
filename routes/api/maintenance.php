<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Maintenance\Controllers\MachineController;
use App\Modules\Maintenance\Controllers\PreventiveScheduleController;
use App\Modules\Maintenance\Controllers\PreventiveTaskController;
use App\Modules\Maintenance\Controllers\BreakdownReportController;
use App\Modules\Maintenance\Controllers\MaintenanceAnalyticsController;

Route::middleware(['auth:jwt', 'permission:maintenance'])->prefix('maintenance')->group(function () {
    Route::apiResource('machines', MachineController::class);
    Route::get('machines/{id}/history', [MachineController::class, 'history']);

    Route::apiResource('preventive-schedules', PreventiveScheduleController::class);
    Route::apiResource('preventive-tasks', PreventiveTaskController::class);
    Route::get('preventive-tasks/due', [PreventiveTaskController::class, 'due']);
    Route::post('preventive-tasks/{id}/complete', [PreventiveTaskController::class, 'complete']);

    Route::apiResource('breakdown-reports', BreakdownReportController::class);
    Route::get('breakdown-reports/open', [BreakdownReportController::class, 'open']);
    Route::post('breakdown-reports/{id}/assign', [BreakdownReportController::class, 'assign']);
    Route::post('breakdown-reports/{id}/resolve', [BreakdownReportController::class, 'resolve']);

    Route::get('analytics/downtime', [MaintenanceAnalyticsController::class, 'downtime']);
    Route::get('analytics/mtbf', [MaintenanceAnalyticsController::class, 'mtbf']);
    Route::get('analytics/mttr', [MaintenanceAnalyticsController::class, 'mttr']);
});
