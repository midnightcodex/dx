<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->middleware(['auth', 'verified']);

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Authentication Routes
Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');

Route::get('/register', function () {
    return Inertia::render('Auth/Register');
})->name('register');

Route::get('/forgot-password', function () {
    return Inertia::render('Auth/ForgotPassword');
})->name('password.request');

// Auth Action Routes
use App\Modules\Auth\Controllers\AuthController;
Route::post('/login', [AuthController::class, 'loginWeb']);
Route::post('/register', [AuthController::class, 'registerWeb']);
Route::post('/forgot-password', [AuthController::class, 'forgotPasswordWeb'])->name('password.email');
Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');

// Manufacturing Module
use App\Http\Controllers\Manufacturing\WorkOrderController;
Route::get('/manufacturing', function () {
    return Inertia::render('Manufacturing/Index');
})->name('manufacturing.index');

Route::get('/manufacturing/bom', function () {
    return Inertia::render('Manufacturing/BOM');
})->name('manufacturing.bom');

// Work Orders (Resource Routes)
Route::get('/manufacturing/work-orders', [WorkOrderController::class, 'index'])->name('manufacturing.work-orders');
Route::get('/manufacturing/work-orders/create', [WorkOrderController::class, 'create'])->name('manufacturing.work-orders.create');
Route::post('/manufacturing/work-orders', [WorkOrderController::class, 'store'])->name('manufacturing.work-orders.store');
Route::get('/manufacturing/work-orders/{id}', [WorkOrderController::class, 'show'])->name('manufacturing.work-orders.show');
Route::post('/manufacturing/work-orders/{id}/release', [WorkOrderController::class, 'release'])->name('manufacturing.work-orders.release');
Route::post('/manufacturing/work-orders/{id}/start', [WorkOrderController::class, 'start'])->name('manufacturing.work-orders.start');

Route::get('/manufacturing/production', function () {
    return Inertia::render('Manufacturing/Production');
})->name('manufacturing.production');

Route::get('/manufacturing/quality', function () {
    return Inertia::render('Manufacturing/Quality');
})->name('manufacturing.quality');

Route::get('/manufacturing/workstations', function () {
    return Inertia::render('Manufacturing/Workstations');
})->name('manufacturing.workstations');

// Inventory Module
Route::get('/inventory', function () {
    return Inertia::render('Inventory/Index');
})->name('inventory.index');

Route::get('/inventory/items', function () {
    return Inertia::render('Inventory/ItemMaster');
})->name('inventory.items');

Route::get('/inventory/stock-ledger', function () {
    return Inertia::render('Inventory/StockLedger');
})->name('inventory.stock-ledger');

Route::get('/inventory/warehouses', function () {
    return Inertia::render('Inventory/Warehouses');
})->name('inventory.warehouses');

Route::get('/inventory/batches', function () {
    return Inertia::render('Inventory/Batches');
})->name('inventory.batches');

// Procurement Module
Route::get('/procurement', function () {
    return Inertia::render('Procurement/Index');
})->name('procurement.index');

Route::get('/procurement/vendors', function () {
    return Inertia::render('Procurement/Vendors');
})->name('procurement.vendors');

Route::get('/procurement/purchase-orders', function () {
    return Inertia::render('Procurement/PurchaseOrders');
})->name('procurement.purchase-orders');

Route::get('/procurement/grn', function () {
    return Inertia::render('Procurement/GRN');
})->name('procurement.grn');

// Sales Module
Route::get('/sales', function () {
    return Inertia::render('Sales/Index');
})->name('sales.index');

Route::get('/sales/customers', function () {
    return Inertia::render('Sales/Customers');
})->name('sales.customers');

Route::get('/sales/orders', function () {
    return Inertia::render('Sales/SalesOrders');
})->name('sales.orders');

Route::get('/sales/delivery', function () {
    return Inertia::render('Sales/Delivery');
})->name('sales.delivery');

// Support Modules
Route::get('/maintenance', function () {
    return Inertia::render('Maintenance/Index');
})->name('maintenance.index');

Route::get('/hr', function () {
    return Inertia::render('HR/Index');
})->name('hr.index');

Route::get('/compliance', function () {
    return Inertia::render('Compliance/Index');
})->name('compliance.index');

// Analytics
Route::get('/reports', function () {
    return Inertia::render('Reports/Index');
})->name('reports.index');

Route::get('/settings', function () {
    return Inertia::render('Settings/Index');
})->name('settings.index');

// Help/Manual
Route::get('/help', function () {
    return Inertia::render('Help/Index');
})->name('help.index');

