<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Manufacturing\WorkOrderController;
use App\Modules\Auth\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => Inertia::render('Auth/Login'))->name('login');
    Route::get('/register', fn() => Inertia::render('Auth/Register'))->name('register');
    Route::get('/forgot-password', fn() => Inertia::render('Auth/ForgotPassword'))->name('password.request');

    Route::post('/login', [AuthController::class, 'loginWeb']);
    Route::post('/register', [AuthController::class, 'registerWeb']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPasswordWeb'])->name('password.email');
});

Route::post('/logout', [AuthController::class, 'logoutWeb'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manufacturing Module
    Route::get('/manufacturing', fn() => Inertia::render('Manufacturing/Index'))->name('manufacturing.index');
    Route::get('/manufacturing/bom', fn() => Inertia::render('Manufacturing/BOM'))->name('manufacturing.bom');

    // Work Orders (Resource Routes)
    Route::get('/manufacturing/work-orders', [WorkOrderController::class, 'index'])->name('manufacturing.work-orders');
    Route::get('/manufacturing/work-orders/create', [WorkOrderController::class, 'create'])->name('manufacturing.work-orders.create');
    Route::post('/manufacturing/work-orders', [WorkOrderController::class, 'store'])->name('manufacturing.work-orders.store');
    Route::get('/manufacturing/work-orders/{id}', [WorkOrderController::class, 'show'])->name('manufacturing.work-orders.show');
    Route::post('/manufacturing/work-orders/{id}/release', [WorkOrderController::class, 'release'])->name('manufacturing.work-orders.release');
    Route::post('/manufacturing/work-orders/{id}/start', [WorkOrderController::class, 'start'])->name('manufacturing.work-orders.start');

    Route::get('/manufacturing/production', fn() => Inertia::render('Manufacturing/Production'))->name('manufacturing.production');
    Route::get('/manufacturing/quality', fn() => Inertia::render('Manufacturing/Quality'))->name('manufacturing.quality');
    Route::get('/manufacturing/workstations', fn() => Inertia::render('Manufacturing/Workstations'))->name('manufacturing.workstations');

    // Inventory Module
    Route::get('/inventory', fn() => Inertia::render('Inventory/Index'))->name('inventory.index');
    Route::get('/inventory/items', fn() => Inertia::render('Inventory/ItemMaster'))->name('inventory.items');
    Route::get('/inventory/stock-ledger', fn() => Inertia::render('Inventory/StockLedger'))->name('inventory.stock-ledger');
    Route::get('/inventory/warehouses', fn() => Inertia::render('Inventory/Warehouses'))->name('inventory.warehouses');
    Route::get('/inventory/batches', fn() => Inertia::render('Inventory/Batches'))->name('inventory.batches');

    // Procurement Module
    Route::get('/procurement', fn() => Inertia::render('Procurement/Index'))->name('procurement.index');
    Route::get('/procurement/vendors', fn() => Inertia::render('Procurement/Vendors'))->name('procurement.vendors');
    Route::get('/procurement/purchase-orders', fn() => Inertia::render('Procurement/PurchaseOrders'))->name('procurement.purchase-orders');
    Route::get('/procurement/grn', fn() => Inertia::render('Procurement/GRN'))->name('procurement.grn');

    // Sales Module
    Route::get('/sales', fn() => Inertia::render('Sales/Index'))->name('sales.index');
    Route::get('/sales/customers', fn() => Inertia::render('Sales/Customers'))->name('sales.customers');
    Route::get('/sales/orders', fn() => Inertia::render('Sales/SalesOrders'))->name('sales.orders');
    Route::get('/sales/delivery', fn() => Inertia::render('Sales/Delivery'))->name('sales.delivery');

    // Support Modules
    Route::get('/maintenance', fn() => Inertia::render('Maintenance/Index'))->name('maintenance.index');
    Route::get('/hr', fn() => Inertia::render('HR/Index'))->name('hr.index');
    Route::get('/compliance', fn() => Inertia::render('Compliance/Index'))->name('compliance.index');

    // Analytics and Settings
    Route::get('/reports', fn() => Inertia::render('Reports/Index'))->name('reports.index');
    Route::get('/settings', fn() => Inertia::render('Settings/Index'))->name('settings.index');

    // Help/Manual
    Route::get('/help', fn() => Inertia::render('Help/Index'))->name('help.index');
});
