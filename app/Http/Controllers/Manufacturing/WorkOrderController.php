<?php

namespace App\Http\Controllers\Manufacturing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Modules\Manufacturing\Services\WorkOrderWebService;

class WorkOrderController extends Controller
{
    public function __construct(private WorkOrderWebService $service)
    {
    }

    /**
     * Display a listing of work orders.
     */
    public function index(Request $request)
    {
        $orgId = auth()->user()->organization_id;
        $filters = $request->only(['status', 'search']);
        $workOrders = $this->service->list($orgId, $filters, 15);

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
        $orgId = auth()->user()->organization_id;
        $data = $this->service->getCreateData($orgId);

        return Inertia::render('Manufacturing/WorkOrderCreate', [
            'items' => $data['items'],
            'boms' => $data['boms'],
            'warehouses' => $data['warehouses'],
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

        $workOrder = $this->service->create(
            auth()->user()->organization_id,
            auth()->id(),
            $validated
        );

        return redirect()->route('manufacturing.work-orders')
            ->with('message', "Work Order {$workOrder->wo_number} created successfully!");
    }

    /**
     * Display the specified work order.
     */
    public function show(string $id)
    {
        $workOrder = $this->service->find(auth()->user()->organization_id, $id);

        return Inertia::render('Manufacturing/WorkOrderShow', [
            'workOrder' => $workOrder,
        ]);
    }

    /**
     * Release a work order for production.
     */
    public function release(string $id)
    {
        $workOrder = $this->service->release(
            auth()->user()->organization_id,
            auth()->id(),
            $id
        );

        if (!$workOrder) {
            return back()->with('error', 'Only PLANNED work orders can be released.');
        }

        // TODO: Add material availability check here
        // TODO: Explode BOM and create work order materials

        return back()->with('message', "Work Order {$workOrder->wo_number} has been released!");
    }

    /**
     * Start production on a work order.
     */
    public function start(string $id)
    {
        $workOrder = $this->service->start(
            auth()->user()->organization_id,
            auth()->id(),
            $id
        );

        if (!$workOrder) {
            return back()->with('error', 'Only RELEASED work orders can be started.');
        }

        return back()->with('message', "Production started on {$workOrder->wo_number}!");
    }
}
