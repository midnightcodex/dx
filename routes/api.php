<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes are loaded by the RouteServiceProvider and all are in the
| "api" middleware group. Keep module routes split under routes/api/.
|--------------------------------------------------------------------------
*/

require __DIR__ . '/api/auth.php';
require __DIR__ . '/api/shared.php';
require __DIR__ . '/api/inventory.php';
require __DIR__ . '/api/inventory_extra.php';
require __DIR__ . '/api/manufacturing.php';
require __DIR__ . '/api/manufacturing_extra.php';
require __DIR__ . '/api/procurement.php';
require __DIR__ . '/api/sales.php';
require __DIR__ . '/api/maintenance.php';
require __DIR__ . '/api/hr.php';
require __DIR__ . '/api/compliance.php';
require __DIR__ . '/api/integrations.php';
require __DIR__ . '/api/reports.php';

Route::fallback(function () {
    $requestId = request()?->attributes?->get('request_id') ?? request()?->header('X-Request-Id');

    return response()->json([
        'success' => false,
        'message' => 'Not Found.',
        'error_code' => \App\Core\Errors\ErrorCodes::NOT_FOUND,
        'request_id' => $requestId,
    ], 404);
});
