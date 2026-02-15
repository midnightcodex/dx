<?php

namespace App\Modules\Manufacturing\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Manufacturing\Models\BomHeader;
use App\Modules\Manufacturing\Services\WorkOrderExecutionService;
use App\Modules\Manufacturing\Services\WorkOrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Core\Errors\ErrorCodes;

class WorkOrderController extends Controller
{
    public function __construct(
        private WorkOrderService $service,
        private WorkOrderExecutionService $executionService
    )
    {
    }

    /**
     * List work orders.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status']);
        $perPage = (int) $request->input('per_page', 15);
        $orgId = auth()->user()->organization_id;

        $paginated = $this->service->list($orgId, $filters, $perPage);

        return $this->success(
            $paginated->items(),
            'Work orders fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    /**
     * Create a new work order.
     */
    public function store(Request $request)
    {
        $orgId = auth()->user()->organization_id;

        $validated = $request->validate([
            'item_id' => [
                'required',
                Rule::exists('inventory.items', 'id')->where(fn($query) => $query->where('organization_id', $orgId)),
            ],
            'bom_id' => [
                'required',
                Rule::exists('manufacturing.bom_headers', 'id')->where(fn($query) => $query->where('organization_id', $orgId)),
            ],
            'planned_quantity' => 'required|numeric|min:0.0001',
            'scheduled_start_date' => 'required|date',
            'scheduled_end_date' => 'nullable|date|after_or_equal:scheduled_start_date',
            'source_warehouse_id' => [
                'required',
                Rule::exists('inventory.warehouses', 'id')->where(fn($query) => $query->where('organization_id', $orgId)),
            ],
            'target_warehouse_id' => [
                'required',
                Rule::exists('inventory.warehouses', 'id')->where(fn($query) => $query->where('organization_id', $orgId)),
            ],
        ]);

        $bom = BomHeader::where('organization_id', $orgId)->find($validated['bom_id']);
        if (!$bom || $bom->item_id !== $validated['item_id']) {
            throw ValidationException::withMessages([
                'bom_id' => ['Selected BOM does not belong to the selected item.'],
            ]);
        }

        $workOrder = $this->service->create(
            $orgId,
            auth()->id(),
            $validated
        );

        return $this->success($workOrder, 'Work order created', 201);
    }

    /**
     * Update work order.
     */
    public function update(Request $request, string $id)
    {
        $orgId = auth()->user()->organization_id;
        $workOrder = $this->service->find($orgId, $id);

        $validated = $request->validate([
            'planned_quantity' => 'sometimes|numeric|min:0.0001',
            'scheduled_start_date' => 'sometimes|date',
            'scheduled_end_date' => 'nullable|date|after_or_equal:scheduled_start_date',
            'source_warehouse_id' => [
                'sometimes',
                Rule::exists('inventory.warehouses', 'id')->where(fn($query) => $query->where('organization_id', $orgId)),
            ],
            'target_warehouse_id' => [
                'sometimes',
                Rule::exists('inventory.warehouses', 'id')->where(fn($query) => $query->where('organization_id', $orgId)),
            ],
            'status' => 'sometimes|in:PLANNED,RELEASED,IN_PROGRESS,COMPLETED,CANCELLED',
        ]);

        $workOrder = $this->service->update($workOrder, auth()->id(), $validated);

        return $this->success($workOrder, 'Work order updated');
    }

    /**
     * Show work order details.
     */
    public function show(string $id)
    {
        $workOrder = $this->service->find(auth()->user()->organization_id, $id);

        return $this->success($workOrder, 'Work order retrieved');
    }

    /**
     * Release work order (allocate materials).
     */
    public function release(string $id)
    {
        $workOrder = $this->service->release(
            auth()->user()->organization_id,
            auth()->id(),
            $id
        );

        if (!$workOrder) {
            return $this->error(
                'Only PLANNED orders can be released',
                400,
                null,
                ErrorCodes::WORK_ORDER_RELEASE_INVALID_STATUS
            );
        }

        return $this->success($workOrder, 'Work order released');
    }

    public function issueMaterials(Request $request, string $id)
    {
        $orgId = $request->user()->organization_id;
        $validated = $request->validate([
            'materials' => 'required|array|min:1',
            'materials.*.work_order_material_id' => [
                'required',
                Rule::exists('manufacturing.work_order_materials', 'id')->where(fn($q) => $q->where('organization_id', $orgId)),
            ],
            'materials.*.quantity' => 'required|numeric|min:0.0001',
            'materials.*.batch_id' => [
                'nullable',
                Rule::exists('inventory.batches', 'id')->where(fn($q) => $q->where('organization_id', $orgId)),
            ],
        ]);

        $wo = $this->executionService->issueMaterials($orgId, $request->user()->id, $id, $validated['materials']);
        return $this->success($wo, 'Materials issued and inventory posted');
    }

    public function recordProduction(Request $request, string $id)
    {
        $orgId = $request->user()->organization_id;
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.0001',
            'quantity_rejected' => 'nullable|numeric|min:0',
            'batch_id' => ['nullable', Rule::exists('inventory.batches', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'batch_number' => 'nullable|string|max:100',
            'rejection_reason' => 'nullable|string|max:255',
            'work_order_operation_id' => ['nullable', Rule::exists('manufacturing.work_order_operations', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'work_center_id' => ['nullable', Rule::exists('manufacturing.work_centers', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'shift_id' => ['nullable', Rule::exists('hr.shifts', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'notes' => 'nullable|string',
        ]);

        $wo = $this->executionService->recordProduction($orgId, $request->user()->id, $id, $validated);
        return $this->success($wo, 'Production recorded and inventory receipt posted');
    }

    public function complete(Request $request, string $id)
    {
        $wo = $this->executionService->completeWorkOrder($request->user()->organization_id, $request->user()->id, $id);
        return $this->success($wo, 'Work order completed');
    }
}
