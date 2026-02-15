<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Compliance\Controllers\AuditLogController;
use App\Modules\Compliance\Controllers\DocumentController;
use App\Modules\Compliance\Controllers\CertificationController;

Route::middleware(['auth:jwt', 'permission:compliance'])->prefix('compliance')->group(function () {
    Route::get('audit-logs', [AuditLogController::class, 'index']);
    Route::get('audit-logs/{id}', [AuditLogController::class, 'show']);
    Route::apiResource('documents', DocumentController::class);
    Route::apiResource('certifications', CertificationController::class);
});
