<?php

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Services\SalesFulfillmentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SalesOrderController extends Controller
{
    public function __construct(private SalesFulfillmentService $service)
    {
    }

    public function index(Request $request)
    {
        $orgId = $request->user()->organization_id;
        $paginated = $this->service->listOrders($orgId, (int) $request->input('per_page', 15));
        return $this->success($paginated->items(), 'Sales orders fetched', 200, $this->paginationMeta($paginated));
    }

    public function store(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $validated = $request->validate([
            'so_number' => 'nullable|string|max:50',
            'customer_id' => ['required', Rule::exists('sales.customers', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'currency' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.line_number' => 'nullable|integer|min:1',
            'lines.*.item_id' => ['required', Rule::exists('inventory.items', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.uom_id' => ['nullable', Rule::exists('shared.uom', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.tax_amount' => 'nullable|numeric|min:0',
            'lines.*.line_amount' => 'nullable|numeric|min:0',
        ]);

        $order = $this->service->createOrder($orgId, $request->user()->id, $validated);
        return $this->success($order, 'Sales order created', 201);
    }

    public function show(Request $request, string $id)
    {
        $order = $this->service->findOrder($request->user()->organization_id, $id);
        return $this->success($order, 'Sales order retrieved');
    }

    public function update(Request $request, string $id)
    {
        $orgId = $request->user()->organization_id;

        $validated = $request->validate([
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $order = $this->service->updateOrder($orgId, $request->user()->id, $id, $validated);
        return $this->success($order, 'Sales order updated');
    }

    public function destroy(Request $request, string $id)
    {
        $order = $this->service->cancelOrder($request->user()->organization_id, $request->user()->id, $id);
        return $this->success($order, 'Sales order cancelled');
    }

    public function confirm(Request $request, string $id)
    {
        $order = $this->service->confirmOrder($request->user()->organization_id, $request->user()->id, $id);
        return $this->success($order, 'Sales order confirmed');
    }

    public function reserveStock(Request $request, string $id)
    {
        $order = $this->service->reserveStock($request->user()->organization_id, $request->user()->id, $id);
        return $this->success($order, 'Stock reserved for sales order');
    }

    public function pendingDispatch(Request $request)
    {
        $paginated = $this->service->pendingDispatch($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success($paginated->items(), 'Pending dispatch sales orders', 200, $this->paginationMeta($paginated));
    }
}
