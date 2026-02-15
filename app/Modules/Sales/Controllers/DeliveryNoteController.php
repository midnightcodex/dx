<?php

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Services\SalesFulfillmentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeliveryNoteController extends Controller
{
    public function __construct(private SalesFulfillmentService $service)
    {
    }

    public function index(Request $request)
    {
        $orgId = $request->user()->organization_id;
        $paginated = $this->service->listDeliveryNotes($orgId, (int) $request->input('per_page', 15));
        return $this->success($paginated->items(), 'Delivery notes fetched', 200, $this->paginationMeta($paginated));
    }

    public function store(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $validated = $request->validate([
            'dn_number' => 'nullable|string|max:50',
            'sales_order_id' => ['required', Rule::exists('sales.sales_orders', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'warehouse_id' => ['required', Rule::exists('inventory.warehouses', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'delivery_date' => 'required|date',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.line_number' => 'nullable|integer|min:1',
            'lines.*.sales_order_line_id' => ['required', Rule::exists('sales.sales_order_lines', 'id')],
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.uom_id' => ['nullable', Rule::exists('shared.uom', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'lines.*.batch_id' => ['nullable', Rule::exists('inventory.batches', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
        ]);

        $note = $this->service->createDeliveryNote($orgId, $request->user()->id, $validated);
        return $this->success($note, 'Delivery note created', 201);
    }

    public function show(Request $request, string $id)
    {
        $note = $this->service->findDeliveryNote($request->user()->organization_id, $id);
        return $this->success($note, 'Delivery note retrieved');
    }

    public function update(Request $request, string $id)
    {
        $note = $this->service->findDeliveryNote($request->user()->organization_id, $id);
        return $this->success($note, 'Delivery note retrieved');
    }

    public function destroy(Request $request, string $id)
    {
        $note = $this->service->findDeliveryNote($request->user()->organization_id, $id);
        if ($note->status !== 'DRAFT') {
            return $this->error('Only DRAFT delivery notes can be deleted.', 422);
        }
        $note->delete();
        return $this->success(null, 'Delivery note deleted');
    }

    public function dispatch(Request $request, string $id)
    {
        $note = $this->service->dispatchDeliveryNote($request->user()->organization_id, $request->user()->id, $id);
        return $this->success($note, 'Delivery note dispatched');
    }
}
