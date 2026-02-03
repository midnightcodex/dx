<?php

namespace App\Http\Controllers\Manufacturing;

use App\Http\Controllers\Controller;
use App\Modules\Manufacturing\Models\WorkOrder;
use App\Modules\Manufacturing\Models\BomHeader;
use App\Modules\Inventory\Models\Item;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class WorkOrderController extends Controller
{
    /**
     * Display a listing of work orders.
     */
    public function index(Request $request)
    {
        $query = WorkOrder::with(['item', 'sourceWarehouse', 'targetWarehouse', 'bom']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', strtoupper($request->status));
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('wo_number', 'ilike', "%{$search}%")
                    ->orWhereHas('item', fn($q) => $q->where('name', 'ilike', "%{$search}%"));
            });
        }

        $workOrders = $query->latest()->paginate(15);

        // Transform for frontend
        $workOrders->getCollection()->transform(function ($wo) {
            return [
                'id' => $wo->id,
                'woNumber' => $wo->wo_number,
                'product' => $wo->item->name ?? 'N/A',
                'quantity' => $wo->planned_quantity,
                'completedQuantity' => $wo->completed_quantity ?? 0,
                'status' => ucwords(strtolower(str_replace('_', ' ', $wo->status))),
                'rawStatus' => $wo->status,
                'scheduledStart' => $wo->scheduled_start_date?->format('Y-m-d'),
                'scheduledEnd' => $wo->scheduled_end_date?->format('Y-m-d'),
                'sourceWarehouse' => $wo->sourceWarehouse->name ?? 'N/A',
                'targetWarehouse' => $wo->targetWarehouse->name ?? 'N/A',
            ];
        });

        return Inertia::render('Manufacturing/WorkOrders', [
            'workOrders' => $workOrders,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new work order.
     */
    public function create()
    {
        // Get available items (finished goods that have BOMs)
        $items = Item::whereHas('bomHeaders', fn($q) => $q->where('is_active', true))
            ->where('is_active', true)
            ->get(['id', 'item_code', 'name']);

        // Get available BOMs
        $boms = BomHeader::where('is_active', true)
            ->with('item:id,name')
            ->get(['id', 'bom_code', 'item_id', 'revision']);

        // Get warehouses
        $warehouses = Warehouse::where('is_active', true)
            ->get(['id', 'code', 'name']);

        return Inertia::render('Manufacturing/WorkOrderCreate', [
            'items' => $items,
            'boms' => $boms,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Store a newly created work order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:inventory.items,id',
            'bom_id' => 'required|exists:manufacturing.bom_headers,id',
            'planned_quantity' => 'required|numeric|min:0.0001',
            'scheduled_start_date' => 'required|date',
            'scheduled_end_date' => 'nullable|date|after_or_equal:scheduled_start_date',
            'source_warehouse_id' => 'required|exists:inventory.warehouses,id',
            'target_warehouse_id' => 'required|exists:inventory.warehouses,id',
            'priority' => 'nullable|in:LOW,NORMAL,HIGH,URGENT',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['organization_id'] = auth()->user()->organization_id;
        $validated['created_by'] = auth()->id();
        $validated['wo_number'] = $this->generateWoNumber();
        $validated['status'] = 'PLANNED';
        $validated['priority'] = $validated['priority'] ?? 'NORMAL';

        $workOrder = WorkOrder::create($validated);

        return redirect()->route('manufacturing.work-orders')
            ->with('message', "Work Order {$workOrder->wo_number} created successfully!");
    }

    /**
     * Display the specified work order.
     */
    public function show(string $id)
    {
        $workOrder = WorkOrder::with([
            'item',
            'bom.lines.item',
            'materials.item',
            'operations',
            'sourceWarehouse',
            'targetWarehouse',
            'createdBy',
        ])->findOrFail($id);

        return Inertia::render('Manufacturing/WorkOrderShow', [
            'workOrder' => $workOrder,
        ]);
    }

    /**
     * Release a work order for production.
     */
    public function release(string $id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        if ($workOrder->status !== 'PLANNED') {
            return back()->with('error', 'Only PLANNED work orders can be released.');
        }

        // TODO: Add material availability check here
        // TODO: Explode BOM and create work order materials

        $workOrder->update([
            'status' => 'RELEASED',
            'released_at' => now(),
            'released_by' => auth()->id(),
        ]);

        return back()->with('message', "Work Order {$workOrder->wo_number} has been released!");
    }

    /**
     * Start production on a work order.
     */
    public function start(string $id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        if ($workOrder->status !== 'RELEASED') {
            return back()->with('error', 'Only RELEASED work orders can be started.');
        }

        $workOrder->update([
            'status' => 'IN_PROGRESS',
            'actual_start_date' => now(),
        ]);

        return back()->with('message', "Production started on {$workOrder->wo_number}!");
    }

    /**
     * Generate a unique work order number.
     */
    private function generateWoNumber(): string
    {
        $prefix = 'WO';
        $year = date('Y');
        $month = date('m');

        // Get the last WO number for this month
        $lastWo = WorkOrder::where('wo_number', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderBy('wo_number', 'desc')
            ->first();

        if ($lastWo) {
            $lastNumber = (int) substr($lastWo->wo_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}-{$year}{$month}-{$nextNumber}";
    }
}
