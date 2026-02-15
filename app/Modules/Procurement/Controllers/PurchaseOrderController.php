<?php

namespace App\Modules\Procurement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Procurement\Services\PurchaseOrderFlowService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    public function __construct(private PurchaseOrderFlowService $service)
    {
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success($paginated->items(), 'Purchase orders fetched', 200, $this->paginationMeta($paginated));
    }

    public function store(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $validated = $request->validate([
            'po_number' => 'nullable|string|max:50',
            'vendor_id' => ['required', Rule::exists('procurement.vendors', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'delivery_warehouse_id' => ['required', Rule::exists('inventory.warehouses', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'currency' => 'nullable|string|max:10',
            'payment_terms' => 'nullable|string|max:50',
            'delivery_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.line_number' => 'nullable|integer|min:1',
            'lines.*.item_id' => ['required', Rule::exists('inventory.items', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'lines.*.description' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.uom_id' => ['nullable', Rule::exists('shared.uom', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.tax_rate' => 'nullable|numeric|min:0',
            'lines.*.discount_percentage' => 'nullable|numeric|min:0',
            'lines.*.expected_date' => 'nullable|date',
        ]);

        $po = $this->service->create($orgId, $request->user()->id, $validated);
        return $this->success($po, 'Purchase order created', 201);
    }

    public function show(Request $request, string $id)
    {
        $po = $this->service->find($request->user()->organization_id, $id);
        return $this->success($po, 'Purchase order retrieved');
    }

    public function update(Request $request, string $id)
    {
        $orgId = $request->user()->organization_id;

        $validated = $request->validate([
            'expected_date' => 'nullable|date',
            'payment_terms' => 'nullable|string|max:50',
            'delivery_address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $po = $this->service->update($orgId, $request->user()->id, $id, $validated);
        return $this->success($po, 'Purchase order updated');
    }

    public function destroy(Request $request, string $id)
    {
        $po = $this->service->cancel($request->user()->organization_id, $request->user()->id, $id);
        return $this->success($po, 'Purchase order cancelled');
    }

    public function submit(Request $request, string $id)
    {
        $po = $this->service->submit($request->user()->organization_id, $request->user()->id, $id);
        return $this->success($po, 'Purchase order submitted');
    }

    public function approve(Request $request, string $id)
    {
        $po = $this->service->approve($request->user()->organization_id, $request->user()->id, $id);
        return $this->success($po, 'Purchase order approved');
    }
}
