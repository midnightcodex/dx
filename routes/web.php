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
use App\Http\Controllers\Sales\SalesOrderController;
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
Route::post('/manufacturing/work-orders/{id}/issue-materials', [WorkOrderController::class, 'issueMaterials'])->name('manufacturing.work-orders.issue-materials');
Route::post('/manufacturing/work-orders/{id}/complete', [WorkOrderController::class, 'complete'])->name('manufacturing.work-orders.complete');

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
use App\Http\Controllers\Procurement\PurchaseOrderController;
use App\Http\Controllers\Procurement\GrnController;

Route::get('/procurement', function () {
    return Inertia::render('Procurement/Index');
})->name('procurement.index');

Route::get('/procurement/vendors', function () {
    return Inertia::render('Procurement/Vendors');
})->name('procurement.vendors');

// Purchase Orders
Route::get('/procurement/purchase-orders', [PurchaseOrderController::class, 'index'])->name('procurement.purchase-orders');
Route::get('/procurement/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('procurement.purchase-orders.create');
Route::post('/procurement/purchase-orders', [PurchaseOrderController::class, 'store'])->name('procurement.purchase-orders.store');
Route::get('/procurement/purchase-orders/{id}', [PurchaseOrderController::class, 'show'])->name('procurement.purchase-orders.show');
Route::post('/procurement/purchase-orders/{id}/submit', [PurchaseOrderController::class, 'submit'])->name('procurement.purchase-orders.submit');
Route::post('/procurement/purchase-orders/{id}/approve', [PurchaseOrderController::class, 'approve'])->name('procurement.purchase-orders.approve');
Route::post('/procurement/purchase-orders/{id}/cancel', [PurchaseOrderController::class, 'cancel'])->name('procurement.purchase-orders.cancel');

// Goods Receipt Notes (GRN)
Route::get('/procurement/grn', [GrnController::class, 'index'])->name('procurement.grn.index');
Route::get('/procurement/grn/create', [GrnController::class, 'create'])->name('procurement.grn.create');
Route::post('/procurement/grn', [GrnController::class, 'store'])->name('procurement.grn.store');
Route::get('/procurement/grn/{id}', [GrnController::class, 'show'])->name('procurement.grn.show');
Route::post('/procurement/grn/{id}/post', [GrnController::class, 'post'])->name('procurement.grn.post');

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

// Maintenance Module Routes
use App\Http\Controllers\Maintenance\EquipmentController;
use App\Http\Controllers\Maintenance\MaintenanceTicketController;

Route::get('/maintenance', [MaintenanceTicketController::class, 'index'])->name('maintenance.index');
Route::post('/maintenance/tickets', [MaintenanceTicketController::class, 'store'])->name('maintenance.tickets.store');
Route::post('/maintenance/tickets/{id}/resolve', [MaintenanceTicketController::class, 'resolve'])->name('maintenance.tickets.resolve');

Route::get('/maintenance/equipment', [EquipmentController::class, 'index'])->name('maintenance.equipment.index');
Route::post('/maintenance/equipment', [EquipmentController::class, 'store'])->name('maintenance.equipment.store');


// HR & Payroll Module
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\LeaveController;
use App\Http\Controllers\HR\PayrollController;

Route::get('/hr', function () {
    return redirect()->route('hr.employees.index'); })->name('hr.index');

// Employees
Route::get('/hr/employees', [EmployeeController::class, 'index'])->name('hr.employees.index');
Route::get('/hr/employees/create', [EmployeeController::class, 'create'])->name('hr.employees.create');
Route::post('/hr/employees', [EmployeeController::class, 'store'])->name('hr.employees.store');

// Leaves
Route::get('/hr/leaves', [LeaveController::class, 'index'])->name('hr.leaves.index');
Route::post('/hr/leaves', [LeaveController::class, 'store'])->name('hr.leaves.store');
Route::post('/hr/leaves/{id}', [LeaveController::class, 'update'])->name('hr.leaves.update');

// Payroll
Route::get('/hr/payroll', [PayrollController::class, 'index'])->name('hr.payroll.index');
Route::post('/hr/payroll', [PayrollController::class, 'store'])->name('hr.payroll.store');
Route::get('/hr/payroll/{id}', [PayrollController::class, 'show'])->name('hr.payroll.show');

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

