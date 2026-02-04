<?php

namespace App\Http\Controllers\Manufacturing;

use App\Http\Controllers\Controller;
use App\Modules\Manufacturing\Models\WorkOrder;
use App\Modules\Manufacturing\Models\WorkOrderMaterial;
use App\Modules\Manufacturing\Models\BomHeader;
use App\Modules\Inventory\Models\Item;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\InventoryPostingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class WorkOrderController extends Controller
{
    protected InventoryPostingService $inventoryService;

    public function __construct(InventoryPostingService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

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
     * Release a work order for production (Explode BOM).
     */
    public function release(string $id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        if ($workOrder->status !== 'PLANNED') {
            return back()->withErrors(['status' => 'Only PLANNED work orders can be released.']);
        }

        return DB::transaction(function () use ($workOrder) {
            // Explode BOM
            $bom = BomHeader::with('lines')->findOrFail($workOrder->bom_id);

            foreach ($bom->lines as $line) {
                // Calculate required qty: (WO Qty * BOM Qty) + Scrap
                $baseRequired = $workOrder->planned_quantity * $line->quantity_per_unit;
                $scrapFactor = 1 + ($line->scrap_percentage / 100);
                $totalRequired = $baseRequired * $scrapFactor;

                WorkOrderMaterial::create([
                    'organization_id' => $workOrder->organization_id,
                    'work_order_id' => $workOrder->id,
                    'item_id' => $line->component_item_id,
                    'required_quantity' => $totalRequired,
                    'issued_quantity' => 0,
                    'warehouse_id' => $workOrder->source_warehouse_id, // Default to source WH
                    'operation_sequence' => $line->operation_sequence,
                    'status' => 'PENDING',
                ]);
            }

            $workOrder->update([
                'status' => 'RELEASED',
                'released_at' => now(),
                'released_by' => auth()->id(),
            ]);

            return back()->with('message', "Work Order {$workOrder->wo_number} released. Materials have been allocated.");
        });
    }

    /**
     * Start production on a work order.
     */
    public function start(string $id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        if ($workOrder->status !== 'RELEASED') {
            return back()->withErrors(['status' => 'Only RELEASED work orders can be started.']);
        }

        $workOrder->update([
            'status' => 'IN_PROGRESS',
            'actual_start_at' => now(), // Corrected column name from 'actual_start_date'
        ]);

        return back()->with('message', "Production started on {$workOrder->wo_number}!");
    }

    /**
     * Issue materials to the work order (Consume Stock).
     */
    public function issueMaterials(Request $request, string $id)
    {
        $request->validate([
            'materials' => 'required|array|min:1',
            'materials.*.id' => 'required|exists:manufacturing.work_order_materials,id',
            'materials.*.quantity' => 'required|numeric|min:0.0001',
        ]);

        $workOrder = WorkOrder::findOrFail($id);

        if (!in_array($workOrder->status, ['RELEASED', 'IN_PROGRESS'])) {
            return back()->withErrors(['status' => 'Cannot issue materials in current status.']);
        }

        return DB::transaction(function () use ($workOrder, $request) {
            foreach ($request->materials as $matData) {
                $material = WorkOrderMaterial::where('work_order_id', $workOrder->id)
                    ->lockForUpdate()
                    ->findOrFail($matData['id']);

                $qtyToIssue = $matData['quantity'];

                // 1. Post Inventory Issue
                $this->inventoryService->post(
                    transactionType: 'ISSUE',
                    itemId: $material->item_id,
                    warehouseId: $material->warehouse_id,
                    quantity: -$qtyToIssue, // Negative for issue
                    unitCost: 0.0, // FIFO/Average cost handled by service logic (or 0 for now)
                    referenceType: 'WORK_ORDER',
                    referenceId: $workOrder->id,
                    organizationId: $workOrder->organization_id
                );

                // 2. Update Consumption Record
                $material->issued_quantity += $qtyToIssue;
                $material->save();
            }

            return back()->with('message', 'Materials issued successfully.');
        });
    }

    /**
     * Complete production (Receive Finished Goods).
     */
    public function complete(Request $request, string $id)
    {
        $request->validate([
            'completed_quantity' => 'required|numeric|min:0.0001',
            'rejected_quantity' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $workOrder = WorkOrder::findOrFail($id);

        if ($workOrder->status !== 'IN_PROGRESS') {
            return back()->withErrors(['status' => 'Work Order must be IN PROGRESS to record output.']);
        }

        return DB::transaction(function () use ($workOrder, $request) {
            $qtyProduced = $request->completed_quantity;

            // 1. Post Inventory Receipt (Finished Goods)
            // We need a cost for the finished good. 
            // For now, we will use a standard cost or the sum of issued material costs. 
            // Simplified: Use 0 or let service handle it if it supports standard cost.
            // Better Integration: Calculate actual material cost from issued materials.
            // For MVP: We will pass an estimated unit cost (e.g. from Item master if available) or 0.

            // Fetch approximate cost from item master (if we add standard_cost later) or use 0.
            $unitCost = 0;

            $this->inventoryService->post(
                transactionType: 'RECEIPT',
                itemId: $workOrder->item_id,
                warehouseId: $workOrder->target_warehouse_id,
                quantity: $qtyProduced,
                unitCost: $unitCost, // TODO: Implement proper costing engine
                referenceType: 'WORK_ORDER',
                referenceId: $workOrder->id,
                organizationId: $workOrder->organization_id
            );

            // 2. Update Work Order
            $workOrder->completed_quantity += $qtyProduced;
            $workOrder->rejected_quantity += ($request->rejected_quantity ?? 0);

            if ($workOrder->completed_quantity >= $workOrder->planned_quantity) {
                $workOrder->status = 'COMPLETED';
                $workOrder->actual_end_at = now();
            }

            $workOrder->save();

            return back()->with('message', "Production recorded: {$qtyProduced} units.");
        });
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
