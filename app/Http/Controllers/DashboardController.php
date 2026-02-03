<?php

namespace App\Http\Controllers;

use App\Modules\Inventory\Models\Item;
use App\Modules\Manufacturing\Models\WorkOrder;
use App\Modules\Procurement\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        $lowStockItems = 0; // Requires stock ledger query, simplifying for now
        $recentItems = Item::with(['primaryUom', 'stockLedgers']) // Assuming we want stock info
            ->where('is_active', true)
            ->take(5)
            ->get()
            ->map(function ($item) {
                // Approximate stock from all ledgers
                $totalStock = $item->stockLedgers->sum('quantity_available');
                return [
                    'id' => $item->id,
                    'itemCode' => $item->item_code,
                    'name' => $item->name,
                    'warehouse' => 'Multiple', // Simplification
                    'quantity' => $totalStock,
                    'unit' => $item->primaryUom->code,
                    'reorderLevel' => $item->reorder_level ?? 0,
                ];
            });

        // 3. Procurement Stats
        $pendingPOs = PurchaseOrder::where('status', 'SUBMITTED')->count();

        // 4. Quality (Placeholder for now as module is simplified)
        $qualityIssues = 0;

        return Inertia::render('Dashboard', [
            'stats' => [
                'activeWorkOrders' => $activeWorkOrders,
                'totalItems' => $totalItems,
                'pendingPOs' => $pendingPOs,
                'qualityIssues' => $qualityIssues,
            ],
            'tables' => [
                'workOrders' => $recentWorkOrders,
                'inventory' => $recentItems,
            ]
        ]);
    }
}
