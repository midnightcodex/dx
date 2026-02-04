<?php

namespace App\Http\Controllers;

use App\Modules\Inventory\Models\Item;
use App\Modules\Manufacturing\Models\WorkOrder;
use App\Modules\Procurement\Models\PurchaseOrder;
use App\Modules\Sales\Models\SalesOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with real data.
     */
    public function index()
    {
        // 1. Manufacturing Stats
        $activeWorkOrders = WorkOrder::whereIn('status', ['RELEASED', 'IN_PROGRESS'])->count();
        $recentWorkOrders = WorkOrder::with('item')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($wo) {
                return [
                    'id' => $wo->id,
                    'woNumber' => $wo->wo_number,
                    'product' => $wo->item->name,
                    'quantity' => $wo->planned_quantity,
                    'status' => ucwords(strtolower(str_replace('_', ' ', $wo->status))),
                    'startDate' => $wo->scheduled_start_date ? $wo->scheduled_start_date->format('Y-m-d') : 'TBD',
                ];
            });

        // 2. Inventory Stats
        $totalItems = Item::where('is_active', true)->count();

        // Low Stock Calculation: Items where total available stock < reorder level
        // This is a bit heavy, so we might restrict to items with reorder_level > 0
        // For MVP, we'll traverse items. In prod, use a optimized subquery.
        $lowStockItems = Item::where('is_active', true)
            ->whereNotNull('reorder_level')
            ->where('reorder_level', '>', 0)
            ->get()
            ->filter(function ($item) {
                $totalStock = $item->stockLedgers()->sum('quantity_available');
                return $totalStock <= $item->reorder_level;
            })
            ->count();

        $recentItems = Item::with(['primaryUom', 'stockLedgers'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($item) {
                $totalStock = $item->stockLedgers->sum('quantity_available');
                return [
                    'id' => $item->id,
                    'itemCode' => $item->item_code,
                    'name' => $item->name,
                    'warehouse' => 'Multiple',
                    'quantity' => $totalStock,
                    'unit' => $item->primaryUom->code,
                    'reorderLevel' => $item->reorder_level ?? 0,
                ];
            });

        // 3. Procurement Stats
        $pendingPOs = PurchaseOrder::whereIn('status', ['SUBMITTED', 'APPROVED'])->count();

        // 4. Sales Stats
        $pendingSalesOrders = SalesOrder::whereIn('status', ['CONFIRMED', 'PARTIAL'])->count();
        $recentSalesOrders = SalesOrder::with('customer')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($so) {
                return [
                    'id' => $so->id,
                    'soNumber' => $so->so_number,
                    'customer' => $so->customer->name,
                    'amount' => $so->total_amount,
                    'status' => $so->status,
                    'date' => $so->order_date ? $so->order_date->format('Y-m-d') : '-',
                ];
            });

        // 5. Maintenance Stats
        $openTickets = \App\Modules\Maintenance\Models\MaintenanceTicket::whereIn('status', ['OPEN', 'IN_PROGRESS'])->count();

        // 6. HR Stats
        $pendingLeaves = \App\Modules\HR\Models\LeaveRequest::where('status', 'PENDING')->count();
        $totalEmployees = \App\Modules\HR\Models\Employee::where('status', 'ACTIVE')->count();

        // 7. Quality (Placeholder)
        $qualityIssues = 0;

        return Inertia::render('Dashboard', [
            'stats' => [
                'activeWorkOrders' => $activeWorkOrders,
                'totalItems' => $totalItems,
                'pendingPOs' => $pendingPOs,
                'pendingSalesOrders' => $pendingSalesOrders,
                'pendingSalesOrders' => $pendingSalesOrders,
                'lowStockItems' => $lowStockItems,
                'openTickets' => $openTickets,
                'pendingLeaves' => $pendingLeaves,
                'totalEmployees' => $totalEmployees,
                'qualityIssues' => $qualityIssues,
            ],
            'tables' => [
                'workOrders' => $recentWorkOrders,
                'inventory' => $recentItems,
                'salesOrders' => $recentSalesOrders,
            ]
        ]);
    }
}
