<?php

namespace App\Modules\Procurement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Procurement\Services\GoodsReceiptFlowService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GoodsReceiptNoteController extends Controller
{
    public function __construct(private GoodsReceiptFlowService $service)
    {
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success($paginated->items(), 'GRNs fetched', 200, $this->paginationMeta($paginated));
    }

    public function store(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $validated = $request->validate([
            'grn_number' => 'nullable|string|max:50',
            'purchase_order_id' => ['required', Rule::exists('procurement.purchase_orders', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'warehouse_id' => ['required', Rule::exists('inventory.warehouses', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'receipt_date' => 'required|date',
            'supplier_invoice_number' => 'nullable|string|max:100',
            'supplier_invoice_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.po_line_id' => ['required', Rule::exists('procurement.purchase_order_lines', 'id')],
            'lines.*.received_quantity' => 'nullable|numeric|min:0',
            'lines.*.accepted_quantity' => 'required|numeric|min:0',
            'lines.*.rejected_quantity' => 'nullable|numeric|min:0',
            'lines.*.rejection_reason' => 'nullable|string|max:255',
            'lines.*.batch_number' => 'nullable|string|max:100',
            'lines.*.manufacturing_date' => 'nullable|date',
            'lines.*.expiry_date' => 'nullable|date',
            'lines.*.unit_price' => 'nullable|numeric|min:0',
            'lines.*.quality_status' => 'nullable|in:PENDING,PASSED,FAILED,PARTIAL',
        ]);

        $grn = $this->service->create($orgId, $request->user()->id, $validated);
        return $this->success($grn, 'GRN created', 201);
    }

    public function show(Request $request, string $id)
    {
        $grn = $this->service->find($request->user()->organization_id, $id);
        return $this->success($grn, 'GRN retrieved');
    }

    public function update(Request $request, string $id)
    {
        $grn = $this->service->find($request->user()->organization_id, $id);
        return $this->success($grn, 'GRN retrieved');
    }

    public function destroy(Request $request, string $id)
    {
        $grn = $this->service->find($request->user()->organization_id, $id);
        if ($grn->status !== 'DRAFT') {
            return $this->error('Only DRAFT GRNs can be deleted', 422);
        }
        $grn->delete();
        return $this->success(null, 'GRN deleted');
    }

    public function complete(Request $request, string $id)
    {
        $grn = $this->service->complete($request->user()->organization_id, $request->user()->id, $id);
        return $this->success($grn, 'GRN completed and inventory posted');
    }
}
