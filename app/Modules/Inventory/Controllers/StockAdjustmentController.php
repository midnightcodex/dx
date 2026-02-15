<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Services\StockAdjustmentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StockAdjustmentController extends Controller
{
    public function __construct(private StockAdjustmentService $service)
    {
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));

        return $this->success($paginated->items(), 'Stock adjustments fetched', 200, $this->paginationMeta($paginated));
    }

    public function store(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $validated = $request->validate([
            'adjustment_number' => 'nullable|string|max:50',
            'warehouse_id' => ['required', Rule::exists('inventory.warehouses', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'adjustment_type' => 'required|string|max:50',
            'reason' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => ['required', Rule::exists('inventory.items', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'lines.*.batch_id' => ['nullable', Rule::exists('inventory.batches', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'lines.*.physical_quantity' => 'required|numeric',
            'lines.*.system_quantity' => 'nullable|numeric',
            'lines.*.unit_cost' => 'nullable|numeric|min:0',
            'lines.*.notes' => 'nullable|string',
        ]);

        $adjustment = $this->service->create($orgId, $request->user()->id, $validated);

        return $this->success($adjustment, 'Stock adjustment created', 201);
    }

    public function show(Request $request, string $id)
    {
        $adjustment = $this->service->find($request->user()->organization_id, $id);
        return $this->success($adjustment, 'Stock adjustment retrieved');
    }

    public function submit(Request $request, string $id)
    {
        $adjustment = $this->service->submitForApproval($request->user()->organization_id, $request->user()->id, $id);
        return $this->success($adjustment, 'Stock adjustment submitted for approval');
    }

    public function approve(Request $request, string $id)
    {
        $adjustment = $this->service->approve($request->user()->organization_id, $request->user()->id, $id);
        return $this->success($adjustment, 'Stock adjustment approved');
    }

    public function post(Request $request, string $id)
    {
        $adjustment = $this->service->post($request->user()->organization_id, $request->user()->id, $id);
        return $this->success($adjustment, 'Stock adjustment posted');
    }

    public function pendingApproval(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        $pending = collect($paginated->items())->filter(fn($a) => $a->status === 'PENDING_APPROVAL')->values();

        return $this->success($pending, 'Pending stock adjustments');
    }
}
