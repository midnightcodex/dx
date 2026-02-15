<?php

namespace App\Modules\Procurement\Services;

use App\Modules\Inventory\Models\Batch;
use App\Modules\Inventory\Models\BatchMovement;
use App\Modules\Inventory\Models\Item;
use App\Modules\Inventory\Services\InventoryPostingService;
use App\Modules\Procurement\Models\GoodsReceiptNote;
use App\Modules\Procurement\Models\GrnLine;
use App\Modules\Procurement\Models\PurchaseOrder;
use App\Modules\Procurement\Models\PurchaseOrderLine;
use App\Modules\Shared\Services\NumberSeriesService;
use Illuminate\Support\Facades\DB;

class GoodsReceiptFlowService
{
    public function __construct(
        private NumberSeriesService $numberSeriesService,
        private InventoryPostingService $inventoryPostingService,
        private PurchaseOrderFlowService $purchaseOrderFlowService
    ) {
    }

    public function list(string $organizationId, int $perPage = 15)
    {
        return GoodsReceiptNote::query()
            ->with('lines')
            ->where('organization_id', $organizationId)
            ->latest()
            ->paginate($perPage);
    }

    public function find(string $organizationId, string $id): GoodsReceiptNote
    {
        return GoodsReceiptNote::query()
            ->with(['lines', 'purchaseOrder'])
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
    }

    public function create(string $organizationId, string $userId, array $data): GoodsReceiptNote
    {
        return DB::transaction(function () use ($organizationId, $userId, $data) {
            $po = PurchaseOrder::query()
                ->where('organization_id', $organizationId)
                ->findOrFail($data['purchase_order_id']);

            if (!in_array($po->status, [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_PARTIAL], true)) {
                throw new \RuntimeException('GRN can only be created for APPROVED/PARTIAL POs.');
            }

            $grn = GoodsReceiptNote::create([
                'organization_id' => $organizationId,
                'grn_number' => $data['grn_number'] ?? $this->numberSeriesService->next(
                    $organizationId,
                    'GRN',
                    ['prefix' => 'GRN-', 'padding' => 4]
                ),
                'purchase_order_id' => $po->id,
                'vendor_id' => $po->vendor_id,
                'warehouse_id' => $data['warehouse_id'],
                'receipt_date' => $data['receipt_date'],
                'status' => GoodsReceiptNote::STATUS_DRAFT,
                'supplier_invoice_number' => $data['supplier_invoice_number'] ?? null,
                'supplier_invoice_date' => $data['supplier_invoice_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'received_by' => $userId,
            ]);

            foreach ($data['lines'] as $index => $line) {
                $poLine = PurchaseOrderLine::query()
                    ->where('organization_id', $organizationId)
                    ->where('purchase_order_id', $po->id)
                    ->findOrFail($line['po_line_id']);

                $accepted = (float) $line['accepted_quantity'];
                $rejected = (float) ($line['rejected_quantity'] ?? 0);
                $received = (float) ($line['received_quantity'] ?? ($accepted + $rejected));

                if (abs(($accepted + $rejected) - $received) > 0.0001) {
                    throw new \RuntimeException('received_quantity must equal accepted + rejected.');
                }

                GrnLine::create([
                    'organization_id' => $organizationId,
                    'grn_id' => $grn->id,
                    'po_line_id' => $poLine->id,
                    'item_id' => $poLine->item_id,
                    'ordered_quantity' => $poLine->quantity,
                    'received_quantity' => $received,
                    'accepted_quantity' => $accepted,
                    'rejected_quantity' => $rejected,
                    'rejection_reason' => $line['rejection_reason'] ?? null,
                    'batch_number' => $line['batch_number'] ?? null,
                    'manufacturing_date' => $line['manufacturing_date'] ?? null,
                    'expiry_date' => $line['expiry_date'] ?? null,
                    'unit_price' => $line['unit_price'] ?? $poLine->unit_price,
                    'quality_status' => $line['quality_status'] ?? 'PENDING',
                ]);
            }

            return $grn->load('lines');
        });
    }

    public function complete(string $organizationId, string $userId, string $grnId): GoodsReceiptNote
    {
        return DB::transaction(function () use ($organizationId, $userId, $grnId) {
            $grn = $this->find($organizationId, $grnId);

            if (!in_array($grn->status, [GoodsReceiptNote::STATUS_DRAFT, GoodsReceiptNote::STATUS_APPROVED], true)) {
                throw new \RuntimeException('Only DRAFT/APPROVED GRNs can be posted.');
            }

            foreach ($grn->lines as $line) {
                if ((float) $line->accepted_quantity <= 0) {
                    continue;
                }

                $item = Item::query()
                    ->where('organization_id', $organizationId)
                    ->findOrFail($line->item_id);

                $batchId = $line->batch_id;
                if ($item->is_batch_tracked) {
                    if (!$batchId) {
                        $batch = Batch::create([
                            'organization_id' => $organizationId,
                            'item_id' => $line->item_id,
                            'batch_number' => $line->batch_number ?: ('B-' . now()->format('ymdHis')),
                            'manufacturing_date' => $line->manufacturing_date,
                            'expiry_date' => $line->expiry_date,
                            'vendor_id' => $grn->vendor_id,
                            'status' => 'ACTIVE',
                            'created_by' => $userId,
                        ]);
                        $batchId = $batch->id;
                        $line->batch_id = $batchId;
                        $line->save();
                    }
                }

                $transaction = $this->inventoryPostingService->post(
                    transactionType: 'RECEIPT',
                    itemId: $line->item_id,
                    warehouseId: $grn->warehouse_id,
                    quantity: (float) $line->accepted_quantity,
                    unitCost: (float) $line->unit_price,
                    referenceType: 'GRN',
                    referenceId: $grn->id,
                    batchId: $batchId,
                    organizationId: $organizationId
                );

                if ($batchId) {
                    BatchMovement::create([
                        'organization_id' => $organizationId,
                        'batch_id' => $batchId,
                        'stock_transaction_id' => $transaction->id,
                        'to_warehouse_id' => $grn->warehouse_id,
                        'quantity' => $line->accepted_quantity,
                        'movement_type' => 'GRN_RECEIPT',
                    ]);
                }

                PurchaseOrderLine::query()
                    ->where('organization_id', $organizationId)
                    ->where('id', $line->po_line_id)
                    ->update([
                        'received_quantity' => DB::raw('received_quantity + ' . (float) $line->accepted_quantity),
                    ]);
            }

            $grn->status = GoodsReceiptNote::STATUS_POSTED;
            $grn->posted_by = $userId;
            $grn->posted_at = now();
            $grn->save();

            $po = PurchaseOrder::query()
                ->where('organization_id', $organizationId)
                ->findOrFail($grn->purchase_order_id);
            $this->purchaseOrderFlowService->updateReceiptStatus($po);

            return $grn->refresh()->load('lines');
        });
    }
}
