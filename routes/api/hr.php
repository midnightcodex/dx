<?php

use Illuminate\Support\Facades\Route;
use App\Modules\HR\Controllers\EmployeeController;
use App\Modules\HR\Controllers\ShiftController;
use App\Modules\HR\Controllers\AttendanceController;

Route::middleware(['auth:jwt', 'permission:hr'])->prefix('hr')->group(function () {
    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('shifts', ShiftController::class);
    Route::get('attendance', [AttendanceController::class, 'index']);
    Route::post('attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::get('attendance/daily-report', [AttendanceController::class, 'dailyReport']);
    Route::get('attendance/monthly-report/{employeeId}', [AttendanceController::class, 'monthlyReport']);
    Route::get('attendance/absent-today', [AttendanceController::class, 'absentToday']);
});
