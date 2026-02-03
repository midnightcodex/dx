<?php

namespace App\Modules\Manufacturing\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Manufacturing\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Modules\Inventory\Services\InventoryPostingService;

class WorkOrderController extends Controller
{
    /**
     * List work orders.
     */
    public function index(Request $request)
    {
        $query = WorkOrder::with(['item', 'bom', 'sourceWarehouse']);

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json(
            $query->latest()->paginate($request->input('per_page', 15))
        );
    }

    /**
     * Create a new work order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:inventory.items,id',
            'bom_id' => 'required|exists:manufacturing.bom_headers,id',
            'planned_quantity' => 'required|numeric|min:0.0001',
            'scheduled_start_date' => 'required|date',
            'source_warehouse_id' => 'required|exists:inventory.warehouses,id',
            'target_warehouse_id' => 'required|exists:inventory.warehouses,id',
        ]);

        $validated['organization_id'] = auth()->user()->organization_id;
        $validated['created_by'] = auth()->id();
        $validated['wo_number'] = 'WO-' . strtoupper(uniqid()); // Should use NumberSeries service
        $validated['status'] = 'PLANNED';

        $workOrder = WorkOrder::create($validated);

        return response()->json($workOrder, 201);
    }

    /**
     * Show work order details.
     */
    public function show(string $id)
    {
        $workOrder = WorkOrder::with(['materials', 'operations', 'item'])
            ->findOrFail($id);

        return response()->json($workOrder);
    }

    /**
     * Release work order (allocate materials).
     */
    public function release(string $id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        if ($workOrder->status !== 'PLANNED') {
            return response()->json(['message' => 'Only PLANNED orders can be released'], 400);
        }

        // Logic to allocate materials would go here

        $workOrder->update(['status' => 'RELEASED']);

        return response()->json($workOrder);
    }
}
