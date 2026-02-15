<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\StockAdjustment;
use App\Modules\Inventory\Models\StockAdjustmentLine;
use App\Modules\Inventory\Models\StockLedger;
use App\Modules\Shared\Services\ApprovalWorkflowService;
use App\Modules\Shared\Services\NumberSeriesService;
use Illuminate\Support\Facades\DB;

class StockAdjustmentService
{
    public function __construct(
        private NumberSeriesService $numberSeriesService,
        private InventoryPostingService $inventoryPostingService,
        private ApprovalWorkflowService $approvalWorkflowService
    ) {
    }

    public function list(string $organizationId, int $perPage = 15)
    {
        return StockAdjustment::query()
            ->with('lines')
            ->where('organization_id', $organizationId)
            ->latest()
            ->paginate($perPage);
    }

    public function find(string $organizationId, string $id): StockAdjustment
    {
        return StockAdjustment::query()
            ->with('lines')
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
    }

    public function create(string $organizationId, string $userId, array $data): StockAdjustment
    {
        return DB::transaction(function () use ($organizationId, $userId, $data) {
            $adjustment = StockAdjustment::create([
                'organization_id' => $organizationId,
                'adjustment_number' => $data['adjustment_number'] ?? $this->numberSeriesService->next(
                    $organizationId,
                    'STOCK_ADJUSTMENT',
                    ['prefix' => 'ADJ-', 'padding' => 6]
                ),
                'warehouse_id' => $data['warehouse_id'],
                'adjustment_type' => $data['adjustment_type'],
                'status' => StockAdjustment::STATUS_DRAFT,
                'reason' => $data['reason'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($data['lines'] as $index => $line) {
                $ledger = StockLedger::query()
                    ->where('organization_id', $organizationId)
                    ->where('warehouse_id', $adjustment->warehouse_id)
                    ->where('item_id', $line['item_id'])
                    ->where('batch_id', $line['batch_id'] ?? null)
                    ->first();

                $systemQty = (float) ($line['system_quantity'] ?? ($ledger?->quantity_available ?? 0));
                $physicalQty = (float) $line['physical_quantity'];
                $difference = $physicalQty - $systemQty;

                StockAdjustmentLine::create([
                    'organization_id' => $organizationId,
                    'stock_adjustment_id' => $adjustment->id,
                    'item_id' => $line['item_id'],
                    'batch_id' => $line['batch_id'] ?? null,
                    'system_quantity' => $systemQty,
                    'actual_quantity' => $physicalQty,
                    'difference' => $difference,
                    'unit_cost' => $line['unit_cost'] ?? ($ledger?->unit_cost ?? 0),
                    'notes' => $line['notes'] ?? null,
                ]);
            }

            return $adjustment->load('lines');
        });
    }

    public function submitForApproval(string $organizationId, string $userId, string $adjustmentId): StockAdjustment
    {
        $adjustment = $this->find($organizationId, $adjustmentId);

        if ($adjustment->status !== StockAdjustment::STATUS_DRAFT) {
            throw new \RuntimeException('Only DRAFT stock adjustments can be submitted.');
        }

        $adjustment->status = StockAdjustment::STATUS_PENDING_APPROVAL;
        $adjustment->save();

        $this->approvalWorkflowService->requestApproval(
            organizationId: $organizationId,
            requestedBy: $userId,
            entityType: 'STOCK_ADJUSTMENT',
            entityId: $adjustment->id,
            fromStatus: StockAdjustment::STATUS_DRAFT,
            toStatus: StockAdjustment::STATUS_APPROVED
        );

        return $adjustment->refresh();
    }

    public function approve(string $organizationId, string $userId, string $adjustmentId): StockAdjustment
    {
        $adjustment = $this->find($organizationId, $adjustmentId);

        if ($adjustment->status !== StockAdjustment::STATUS_PENDING_APPROVAL) {
            throw new \RuntimeException('Only PENDING_APPROVAL adjustments can be approved.');
        }

        $adjustment->status = StockAdjustment::STATUS_APPROVED;
        $adjustment->approved_by = $userId;
        $adjustment->approved_at = now();
        $adjustment->save();

        return $adjustment->refresh();
    }

    public function post(string $organizationId, string $userId, string $adjustmentId): StockAdjustment
    {
        return DB::transaction(function () use ($organizationId, $userId, $adjustmentId) {
            $adjustment = $this->find($organizationId, $adjustmentId);

            if ($adjustment->status !== StockAdjustment::STATUS_APPROVED) {
                throw new \RuntimeException('Only APPROVED adjustments can be posted.');
            }

            foreach ($adjustment->lines as $line) {
                if ((float) $line->difference == 0.0) {
                    continue;
                }

                $this->inventoryPostingService->post(
                    transactionType: 'ADJUSTMENT',
                    itemId: $line->item_id,
                    warehouseId: $adjustment->warehouse_id,
                    quantity: (float) $line->difference,
                    unitCost: (float) $line->unit_cost,
                    referenceType: 'STOCK_ADJUSTMENT',
                    referenceId: $adjustment->id,
                    batchId: $line->batch_id,
                    organizationId: $organizationId
                );
            }

            $adjustment->status = StockAdjustment::STATUS_POSTED;
            $adjustment->posted_at = now();
            $adjustment->save();

            return $adjustment->refresh();
        });
    }
}
