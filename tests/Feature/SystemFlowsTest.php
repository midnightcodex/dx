<?php

namespace Tests\Feature;

use App\Modules\Inventory\Models\StockAdjustment;
use App\Modules\Inventory\Models\StockLedger;
use App\Modules\Inventory\Models\StockTransaction;
use App\Modules\Inventory\Services\InventoryPostingService;
use App\Modules\Inventory\Services\StockAdjustmentService;
use App\Modules\Manufacturing\Models\BomHeader;
use App\Modules\Manufacturing\Models\BomLine;
use App\Modules\Manufacturing\Models\ScrapEntry;
use App\Modules\Manufacturing\Models\WorkOrder;
use App\Modules\Manufacturing\Models\WorkOrderMaterial;
use App\Modules\Manufacturing\Services\ScrapService;
use App\Modules\Manufacturing\Services\WorkOrderExecutionService;
use App\Modules\Manufacturing\Services\WorkOrderService;
use App\Modules\Procurement\Services\GoodsReceiptFlowService;
use App\Modules\Procurement\Services\PurchaseOrderFlowService;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Models\SalesOrderLine;
use App\Modules\Sales\Services\SalesFulfillmentService;
use App\Modules\Shared\Models\ApprovalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\BuildsErpContext;
use Tests\TestCase;

class SystemFlowsTest extends TestCase
{
    use RefreshDatabase;
    use BuildsErpContext;

    public function test_item_to_po_to_grn_updates_ledger(): void
    {
        $org = $this->createOrganization();
        $user = $this->createSuperAdminUser($org);
        $this->actingAsJwt($user);

        $uom = $this->createUom($org, ['symbol' => 'EA']);
        $warehouse = $this->createWarehouse($org, ['code' => 'RM-01']);
        $item = $this->createItem($org, $uom, ['item_code' => 'RM-STEEL-01', 'name' => 'Steel Sheet']);
        $vendor = $this->createVendor($org);

        $poService = app(PurchaseOrderFlowService::class);
        $grnService = app(GoodsReceiptFlowService::class);

        $po = $poService->create($org->id, $user->id, [
            'vendor_id' => $vendor->id,
            'order_date' => now()->toDateString(),
            'delivery_warehouse_id' => $warehouse->id,
            'lines' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 100,
                    'uom_id' => $uom->id,
                    'unit_price' => 15.5,
                ],
            ],
        ]);
        $po = $poService->submit($org->id, $user->id, $po->id);
        $po = $poService->approve($org->id, $user->id, $po->id);
        $poLine = $po->lines()->firstOrFail();

        $grn = $grnService->create($org->id, $user->id, [
            'purchase_order_id' => $po->id,
            'warehouse_id' => $warehouse->id,
            'receipt_date' => now()->toDateString(),
            'lines' => [
                [
                    'po_line_id' => $poLine->id,
                    'accepted_quantity' => 100,
                    'received_quantity' => 100,
                    'unit_price' => 15.5,
                ],
            ],
        ]);
        $grn = $grnService->complete($org->id, $user->id, $grn->id);

        $ledger = StockLedger::query()
            ->where('organization_id', $org->id)
            ->where('item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->first();

        $this->assertNotNull($ledger);
        $this->assertSame(100.0, (float) $ledger->quantity_available);

        $transaction = StockTransaction::query()
            ->where('organization_id', $org->id)
            ->where('reference_type', 'GRN')
            ->where('reference_id', $grn->id)
            ->where('transaction_type', 'RECEIPT')
            ->first();

        $this->assertNotNull($transaction);
        $this->assertSame(100.0, (float) $transaction->quantity);
    }

    public function test_item_to_bom_to_work_order_issue_and_receipt_flow(): void
    {
        $org = $this->createOrganization();
        $user = $this->createSuperAdminUser($org);
        $this->actingAsJwt($user);

        $uom = $this->createUom($org, ['symbol' => 'PCS']);
        $sourceWarehouse = $this->createWarehouse($org, ['code' => 'SRC-01']);
        $targetWarehouse = $this->createWarehouse($org, ['code' => 'FG-01']);

        $component = $this->createItem($org, $uom, ['item_code' => 'RM-COMP-01', 'name' => 'Component']);
        $finished = $this->createItem($org, $uom, [
            'item_code' => 'FG-ITEM-01',
            'name' => 'Finished Item',
            'stock_type' => 'FINISHED_GOOD',
        ]);

        $workOrderService = app(WorkOrderService::class);
        $executionService = app(WorkOrderExecutionService::class);

        app(InventoryPostingService::class)->post(
            transactionType: 'RECEIPT',
            itemId: $component->id,
            warehouseId: $sourceWarehouse->id,
            quantity: 500,
            unitCost: 5,
            referenceType: 'SEED',
            referenceId: (string) Str::uuid(),
            organizationId: $org->id
        );

        $bom = BomHeader::query()->create([
            'organization_id' => $org->id,
            'item_id' => $finished->id,
            'bom_number' => 'BOM-FG-01',
            'version' => 1,
            'is_active' => true,
            'base_quantity' => 1,
            'uom_id' => $uom->id,
            'created_by' => $user->id,
        ]);
        BomLine::query()->create([
            'organization_id' => $org->id,
            'bom_header_id' => $bom->id,
            'line_number' => 1,
            'component_item_id' => $component->id,
            'quantity_per_unit' => 2,
        ]);
        $workOrder = $workOrderService->create($org->id, $user->id, [
            'item_id' => $finished->id,
            'bom_id' => $bom->id,
            'planned_quantity' => 50,
            'scheduled_start_date' => now()->toDateString(),
            'source_warehouse_id' => $sourceWarehouse->id,
            'target_warehouse_id' => $targetWarehouse->id,
        ]);
        $workOrder = $workOrderService->release($org->id, $user->id, $workOrder->id);
        $workOrderId = $workOrder->id;

        $woMaterial = WorkOrderMaterial::query()
            ->where('organization_id', $org->id)
            ->where('work_order_id', $workOrderId)
            ->firstOrFail();

        $executionService->issueMaterials($org->id, $user->id, $workOrderId, [
            [
                'work_order_material_id' => $woMaterial->id,
                'quantity' => 100,
            ],
        ]);

        $executionService->recordProduction($org->id, $user->id, $workOrderId, [
            'quantity' => 50,
            'quantity_rejected' => 0,
        ]);

        $executionService->completeWorkOrder($org->id, $user->id, $workOrderId);

        $rawLedger = StockLedger::query()
            ->where('organization_id', $org->id)
            ->where('item_id', $component->id)
            ->where('warehouse_id', $sourceWarehouse->id)
            ->firstOrFail();
        $this->assertSame(400.0, (float) $rawLedger->quantity_available);

        $fgLedger = StockLedger::query()
            ->where('organization_id', $org->id)
            ->where('item_id', $finished->id)
            ->where('warehouse_id', $targetWarehouse->id)
            ->firstOrFail();
        $this->assertSame(50.0, (float) $fgLedger->quantity_available);

        $issueTx = StockTransaction::query()
            ->where('organization_id', $org->id)
            ->where('transaction_type', 'PRODUCTION_ISSUE')
            ->where('reference_type', 'WORK_ORDER')
            ->where('reference_id', $workOrderId)
            ->exists();
        $receiptTx = StockTransaction::query()
            ->where('organization_id', $org->id)
            ->where('transaction_type', 'PRODUCTION_RECEIPT')
            ->where('reference_type', 'WORK_ORDER')
            ->where('reference_id', $workOrderId)
            ->exists();
        $workOrder = WorkOrder::query()->findOrFail($workOrderId);

        $this->assertTrue($issueTx);
        $this->assertTrue($receiptTx);
        $this->assertSame(WorkOrder::STATUS_COMPLETED, $workOrder->status);
    }

    public function test_sales_order_reservation_delivery_issue_flow(): void
    {
        $org = $this->createOrganization();
        $user = $this->createSuperAdminUser($org);
        $this->actingAsJwt($user);

        $uom = $this->createUom($org, ['symbol' => 'PCS']);
        $warehouse = $this->createWarehouse($org, ['code' => 'FG-SALES']);
        $item = $this->createItem($org, $uom, [
            'item_code' => 'FG-SALES-01',
            'name' => 'Sellable Item',
            'stock_type' => 'FINISHED_GOOD',
        ]);
        $customer = $this->createCustomer($org);

        $salesService = app(SalesFulfillmentService::class);

        app(InventoryPostingService::class)->post(
            transactionType: 'RECEIPT',
            itemId: $item->id,
            warehouseId: $warehouse->id,
            quantity: 80,
            unitCost: 12,
            referenceType: 'SEED',
            referenceId: (string) Str::uuid(),
            organizationId: $org->id
        );

        $order = $salesService->createOrder($org->id, $user->id, [
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'lines' => [
                [
                    'line_number' => 1,
                    'item_id' => $item->id,
                    'quantity' => 30,
                    'uom_id' => $uom->id,
                    'unit_price' => 20,
                ],
            ],
        ]);
        $order = $salesService->confirmOrder($org->id, $user->id, $order->id);
        $order = $salesService->reserveStock($org->id, $user->id, $order->id);
        $orderId = $order->id;

        $orderLine = SalesOrderLine::query()
            ->where('organization_id', $org->id)
            ->where('sales_order_id', $orderId)
            ->firstOrFail();
        $this->assertSame(30.0, (float) $orderLine->reserved_quantity);

        $note = $salesService->createDeliveryNote($org->id, $user->id, [
            'sales_order_id' => $orderId,
            'warehouse_id' => $warehouse->id,
            'delivery_date' => now()->toDateString(),
            'lines' => [
                [
                    'line_number' => 1,
                    'sales_order_line_id' => $orderLine->id,
                    'quantity' => 30,
                    'uom_id' => $uom->id,
                    'batch_id' => null,
                ],
            ],
        ]);
        $note = $salesService->dispatchDeliveryNote($org->id, $user->id, $note->id);
        $dnId = $note->id;

        $ledger = StockLedger::query()
            ->where('organization_id', $org->id)
            ->where('item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->firstOrFail();
        $this->assertSame(50.0, (float) $ledger->quantity_available);
        $this->assertSame(0.0, (float) $ledger->quantity_reserved);

        $issueTx = StockTransaction::query()
            ->where('organization_id', $org->id)
            ->where('transaction_type', 'ISSUE')
            ->where('reference_type', 'DELIVERY_NOTE')
            ->where('reference_id', $dnId)
            ->first();
        $this->assertNotNull($issueTx);
        $this->assertSame(-30.0, (float) $issueTx->quantity);

        $orderLine->refresh();
        $order = SalesOrder::query()->findOrFail($orderId);
        $this->assertSame(30.0, (float) $orderLine->dispatched_quantity);
        $this->assertSame(SalesOrder::STATUS_COMPLETED, $order->status);
    }

    public function test_stock_adjustment_approval_and_posting_flow(): void
    {
        $org = $this->createOrganization();
        $user = $this->createSuperAdminUser($org);
        $this->actingAsJwt($user);

        $uom = $this->createUom($org);
        $warehouse = $this->createWarehouse($org, ['code' => 'ADJ-01']);
        $item = $this->createItem($org, $uom, ['item_code' => 'RM-ADJ-01']);
        $adjustmentService = app(StockAdjustmentService::class);

        app(InventoryPostingService::class)->post(
            transactionType: 'RECEIPT',
            itemId: $item->id,
            warehouseId: $warehouse->id,
            quantity: 10,
            unitCost: 9,
            referenceType: 'SEED',
            referenceId: (string) Str::uuid(),
            organizationId: $org->id
        );

        $adjustment = $adjustmentService->create($org->id, $user->id, [
            'warehouse_id' => $warehouse->id,
            'adjustment_type' => 'PHYSICAL_COUNT',
            'reason' => 'Cycle count variance',
            'lines' => [
                [
                    'item_id' => $item->id,
                    'physical_quantity' => 7,
                ],
            ],
        ]);
        $adjustment = $adjustmentService->submitForApproval($org->id, $user->id, $adjustment->id);
        $adjustment = $adjustmentService->approve($org->id, $user->id, $adjustment->id);
        $adjustment = $adjustmentService->post($org->id, $user->id, $adjustment->id);
        $adjustmentId = $adjustment->id;

        $adjustment = StockAdjustment::query()->findOrFail($adjustmentId);
        $this->assertSame(StockAdjustment::STATUS_POSTED, $adjustment->status);

        $ledger = StockLedger::query()
            ->where('organization_id', $org->id)
            ->where('item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->firstOrFail();
        $this->assertSame(7.0, (float) $ledger->quantity_available);

        $tx = StockTransaction::query()
            ->where('organization_id', $org->id)
            ->where('reference_type', 'STOCK_ADJUSTMENT')
            ->where('reference_id', $adjustmentId)
            ->where('transaction_type', 'ADJUSTMENT')
            ->first();
        $this->assertNotNull($tx);
        $this->assertSame(-3.0, (float) $tx->quantity);

        $approval = ApprovalRequest::query()
            ->where('organization_id', $org->id)
            ->where('entity_type', 'STOCK_ADJUSTMENT')
            ->where('entity_id', $adjustmentId)
            ->first();
        $this->assertNotNull($approval);
    }

    public function test_scrap_creates_transaction_and_updates_ledger(): void
    {
        $org = $this->createOrganization();
        $user = $this->createSuperAdminUser($org);
        $this->actingAsJwt($user);

        $uom = $this->createUom($org);
        $warehouse = $this->createWarehouse($org, ['code' => 'SCRAP-01']);
        $item = $this->createItem($org, $uom, ['item_code' => 'SCRAP-ITEM-01']);
        $scrapService = app(ScrapService::class);

        app(InventoryPostingService::class)->post(
            transactionType: 'RECEIPT',
            itemId: $item->id,
            warehouseId: $warehouse->id,
            quantity: 20,
            unitCost: 4,
            referenceType: 'SEED',
            referenceId: (string) Str::uuid(),
            organizationId: $org->id
        );

        $scrap = $scrapService->create($org->id, $user->id, [
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'scrap_quantity' => 4,
            'scrap_reason' => 'Damaged material',
            'scrap_category' => 'PROCESS',
        ]);
        $scrap = ScrapEntry::query()->findOrFail($scrap->id);
        $this->assertNotNull($scrap->inventory_transaction_id);

        $tx = StockTransaction::query()
            ->where('organization_id', $org->id)
            ->where('id', $scrap->inventory_transaction_id)
            ->first();
        $this->assertNotNull($tx);
        $this->assertSame('SCRAP', $tx->transaction_type);
        $this->assertSame(-4.0, (float) $tx->quantity);

        $ledger = StockLedger::query()
            ->where('organization_id', $org->id)
            ->where('item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->firstOrFail();
        $this->assertSame(16.0, (float) $ledger->quantity_available);
    }

    public function test_cancel_transaction_creates_reversal_and_restores_balance(): void
    {
        $org = $this->createOrganization();
        $user = $this->createSuperAdminUser($org);
        $this->actingAsJwt($user);

        $uom = $this->createUom($org);
        $warehouse = $this->createWarehouse($org, ['code' => 'REV-01']);
        $item = $this->createItem($org, $uom, ['item_code' => 'REV-ITEM-01']);

        $service = app(InventoryPostingService::class);

        $original = $service->post(
            transactionType: 'RECEIPT',
            itemId: $item->id,
            warehouseId: $warehouse->id,
            quantity: 10,
            unitCost: 3,
            referenceType: 'MANUAL',
            referenceId: (string) Str::uuid(),
            organizationId: $org->id
        );

        $reversal = $service->cancelTransaction($original->id, 'Cancellation test', $org->id);

        $original->refresh();
        $this->assertTrue((bool) $original->is_cancelled);
        $this->assertSame('CANCELLATION', $reversal->reference_type);
        $this->assertSame(-10.0, (float) $reversal->quantity);

        $ledger = StockLedger::query()
            ->where('organization_id', $org->id)
            ->where('item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->firstOrFail();
        $this->assertSame(0.0, (float) $ledger->quantity_available);
    }

    public function test_multi_tenant_isolation_blocks_cross_org_resource_access(): void
    {
        $orgA = $this->createOrganization(['code' => 'ORG-A']);
        $orgB = $this->createOrganization(['code' => 'ORG-B']);

        $userA = $this->createSuperAdminUser($orgA, ['email' => 'a@example.test']);
        $userB = $this->createSuperAdminUser($orgB, ['email' => 'b@example.test']);

        $uomA = $this->createUom($orgA, ['symbol' => 'A-UOM']);
        $itemA = $this->createItem($orgA, $uomA, ['item_code' => 'ORG-A-ITEM']);

        $this->actingAsJwt($userB);
        $this->getJson("/api/inventory/items/{$itemA->id}")->assertStatus(404);

        $this->actingAsJwt($userA);
        $this->getJson("/api/inventory/items/{$itemA->id}")->assertOk();
    }
}
