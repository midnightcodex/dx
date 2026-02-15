<?php

namespace App\Modules\Manufacturing\Services;

use App\Modules\Inventory\Models\Batch;
use App\Modules\Inventory\Models\StockLedger;
use App\Modules\Inventory\Services\InventoryPostingService;
use App\Modules\Manufacturing\Models\ProductionLog;
use App\Modules\Manufacturing\Models\WorkOrder;
use App\Modules\Manufacturing\Models\WorkOrderMaterial;
use App\Modules\Shared\Services\NumberSeriesService;
use Illuminate\Support\Facades\DB;

class WorkOrderExecutionService
{
    public function __construct(
        private InventoryPostingService $inventoryPostingService,
        private NumberSeriesService $numberSeriesService
    ) {
    }

    public function issueMaterials(string $organizationId, string $userId, string $workOrderId, array $materials): WorkOrder
    {
        return DB::transaction(function () use ($organizationId, $userId, $workOrderId, $materials) {
            $workOrder = WorkOrder::query()
                ->with('materials')
                ->where('organization_id', $organizationId)
                ->findOrFail($workOrderId);

            if (!in_array($workOrder->status, [WorkOrder::STATUS_RELEASED, WorkOrder::STATUS_IN_PROGRESS], true)) {
                throw new \RuntimeException('Materials can only be issued for RELEASED/IN_PROGRESS work orders.');
            }

            foreach ($materials as $materialInput) {
                $woMaterial = WorkOrderMaterial::query()
                    ->where('organization_id', $organizationId)
                    ->where('work_order_id', $workOrder->id)
                    ->findOrFail($materialInput['work_order_material_id']);

                $qtyToIssue = (float) $materialInput['quantity'];
                if ($qtyToIssue <= 0) {
                    throw new \RuntimeException('Issue quantity must be positive.');
                }

                $ledger = StockLedger::query()
                    ->where('organization_id', $organizationId)
                    ->where('item_id', $woMaterial->item_id)
                    ->where('warehouse_id', $woMaterial->warehouse_id ?: $workOrder->source_warehouse_id)
                    ->where('batch_id', $materialInput['batch_id'] ?? $woMaterial->batch_id)
                    ->first();

                $this->inventoryPostingService->post(
                    transactionType: 'PRODUCTION_ISSUE',
                    itemId: $woMaterial->item_id,
                    warehouseId: $woMaterial->warehouse_id ?: $workOrder->source_warehouse_id,
                    quantity: -$qtyToIssue,
                    unitCost: (float) ($ledger?->unit_cost ?? 0),
                    referenceType: 'WORK_ORDER',
                    referenceId: $workOrder->id,
                    batchId: $materialInput['batch_id'] ?? $woMaterial->batch_id,
                    organizationId: $organizationId
                );

                $woMaterial->issued_quantity = (float) $woMaterial->issued_quantity + $qtyToIssue;
                if ((float) $woMaterial->issued_quantity + 0.0001 >= (float) $woMaterial->required_quantity) {
                    $woMaterial->status = 'ISSUED';
                }
                if (!empty($materialInput['batch_id'])) {
                    $woMaterial->batch_id = $materialInput['batch_id'];
                }
                $woMaterial->save();
            }

            if ($workOrder->status === WorkOrder::STATUS_RELEASED) {
                $workOrder->status = WorkOrder::STATUS_IN_PROGRESS;
                $workOrder->actual_start_at = now();
                $workOrder->updated_by = $userId;
                $workOrder->save();
            }

            return $workOrder->refresh()->load('materials');
        });
    }

    public function recordProduction(string $organizationId, string $userId, string $workOrderId, array $data): WorkOrder
    {
        return DB::transaction(function () use ($organizationId, $userId, $workOrderId, $data) {
            $workOrder = WorkOrder::query()
                ->with(['item', 'materials'])
                ->where('organization_id', $organizationId)
                ->findOrFail($workOrderId);

            if (!in_array($workOrder->status, [WorkOrder::STATUS_RELEASED, WorkOrder::STATUS_IN_PROGRESS], true)) {
                throw new \RuntimeException('Production can only be recorded for RELEASED/IN_PROGRESS work orders.');
            }

            $qtyProduced = (float) $data['quantity'];
            $qtyRejected = (float) ($data['quantity_rejected'] ?? 0);
            if ($qtyProduced <= 0) {
                throw new \RuntimeException('Produced quantity must be positive.');
            }

            $batchId = $data['batch_id'] ?? null;
            if (!$batchId && $workOrder->item?->is_batch_tracked) {
                $batch = Batch::create([
                    'organization_id' => $organizationId,
                    'item_id' => $workOrder->item_id,
                    'batch_number' => $data['batch_number'] ?? $this->numberSeriesService->next(
                        $organizationId,
                        'BATCH',
                        ['prefix' => 'BATCH-', 'padding' => 4]
                    ),
                    'manufacturing_date' => now()->toDateString(),
                    'status' => 'ACTIVE',
                    'created_by' => $userId,
                ]);
                $batchId = $batch->id;
            }

            $unitCost = $this->calculateUnitCost($organizationId, $workOrder->id, $qtyProduced);

            $this->inventoryPostingService->post(
                transactionType: 'PRODUCTION_RECEIPT',
                itemId: $workOrder->item_id,
                warehouseId: $workOrder->target_warehouse_id,
                quantity: $qtyProduced,
                unitCost: $unitCost,
                referenceType: 'WORK_ORDER',
                referenceId: $workOrder->id,
                batchId: $batchId,
                organizationId: $organizationId
            );

            ProductionLog::create([
                'organization_id' => $organizationId,
                'work_order_id' => $workOrder->id,
                'work_order_operation_id' => $data['work_order_operation_id'] ?? null,
                'work_center_id' => $data['work_center_id'] ?? null,
                'quantity_produced' => $qtyProduced,
                'quantity_rejected' => $qtyRejected,
                'rejection_reason' => $data['rejection_reason'] ?? null,
                'production_date' => now(),
                'shift_id' => $data['shift_id'] ?? null,
                'operator_id' => $userId,
                'notes' => $data['notes'] ?? null,
            ]);

            $workOrder->completed_quantity = (float) $workOrder->completed_quantity + $qtyProduced;
            $workOrder->rejected_quantity = (float) $workOrder->rejected_quantity + $qtyRejected;
            if ($workOrder->status === WorkOrder::STATUS_RELEASED) {
                $workOrder->status = WorkOrder::STATUS_IN_PROGRESS;
                $workOrder->actual_start_at = now();
            }
            $workOrder->updated_by = $userId;
            $workOrder->save();

            return $workOrder->refresh();
        });
    }

    public function completeWorkOrder(string $organizationId, string $userId, string $workOrderId): WorkOrder
    {
        $workOrder = WorkOrder::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($workOrderId);

        if ($workOrder->status !== WorkOrder::STATUS_IN_PROGRESS) {
            throw new \RuntimeException('Only IN_PROGRESS work orders can be completed.');
        }

        if ((float) $workOrder->completed_quantity + 0.0001 < (float) $workOrder->planned_quantity) {
            throw new \RuntimeException('Cannot complete work order before planned quantity is produced.');
        }

        $workOrder->status = WorkOrder::STATUS_COMPLETED;
        $workOrder->actual_end_at = now();
        $workOrder->updated_by = $userId;
        $workOrder->save();

        return $workOrder->refresh();
    }

    private function calculateUnitCost(string $organizationId, string $workOrderId, float $qtyProduced): float
    {
        if ($qtyProduced <= 0) {
            return 0.0;
        }

        $issuedValue = DB::table('inventory.stock_transactions')
            ->where('organization_id', $organizationId)
            ->where('reference_type', 'WORK_ORDER')
            ->where('reference_id', $workOrderId)
            ->where('transaction_type', 'PRODUCTION_ISSUE')
            ->selectRaw('COALESCE(SUM(ABS(total_value)), 0) as v')
            ->value('v');

        return (float) $issuedValue / $qtyProduced;
    }
}
