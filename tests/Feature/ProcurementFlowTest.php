<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Auth\Models\User;
use App\Modules\Shared\Models\Organization;
use App\Modules\Procurement\Models\Vendor;
use App\Modules\Procurement\Models\PurchaseOrder;
use App\Modules\Procurement\Models\PurchaseOrderLine;
use App\Modules\Procurement\Models\GoodsReceiptNote;
use App\Modules\Inventory\Models\Item;
use App\Modules\Inventory\Models\Warehouse;

class ProcurementFlowTest extends TestCase
{
    // Note: Not using RefreshDatabase to rely on seeded data

    public function test_procure_to_pay_flow()
    {
        // 1. Setup User and Data
        $user = User::where('email', 'admin@examp.com')->first();
        $this->actingAs($user);

        $vendor = Vendor::where('vendor_code', 'V-STEEL')->firstOrFail();
        $warehouse = Warehouse::where('code', 'WH-MAIN')->firstOrFail();
        $item = Item::where('item_code', 'RM-STEEL-2MM')->firstOrFail();

        // 2. Create Purchase Order
        $payload = [
            'vendor_id' => $vendor->id,
            'expected_date' => now()->addDays(7)->format('Y-m-d'),
            'delivery_warehouse_id' => $warehouse->id,
            'payment_terms' => 'NET30',
            'lines' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 100, // Ordering 100 units
                    'unit_price' => 50,
                    'tax_rate' => 18,
                ]
            ]
        ];

        $response = $this->post(route('procurement.purchase-orders.store'), $payload);
        $response->assertRedirect();

        // Get the created PO
        $po = PurchaseOrder::latest()->first();
        $this->assertEquals(PurchaseOrder::STATUS_DRAFT, $po->status);

        // 3. Submit and Approve PO
        $this->post(route('procurement.purchase-orders.submit', $po->id));
        $this->post(route('procurement.purchase-orders.approve', $po->id));

        $po->refresh();
        $this->assertEquals(PurchaseOrder::STATUS_APPROVED, $po->status);

        // 4. Create GRN (Receive Goods)
        $grnPayload = [
            'purchase_order_id' => $po->id,
            'supplier_invoice_number' => 'INV-TEST-001',
            'lines' => [
                [
                    'po_line_id' => $po->lines->first()->id,
                    'received_quantity' => 100, // Fully receiving
                    'accepted_quantity' => 100,
                    'rejected_quantity' => 0,
                ]
            ]
        ];

        $response = $this->post(route('procurement.grn.store'), $grnPayload);
        $response->assertRedirect();

        $grn = GoodsReceiptNote::latest()->first();
        $this->assertEquals(GoodsReceiptNote::STATUS_DRAFT, $grn->status);

        // 5. Post GRN (Update Inventory)
        // Store initial stock
        $initialStock = \App\Modules\Inventory\Models\StockLedger::where('item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->sum('quantity_available');

        $response = $this->post(route('procurement.grn.post', $grn->id));
        $response->assertRedirect();

        $grn->refresh();
        $this->assertEquals(GoodsReceiptNote::STATUS_POSTED, $grn->status);

        // Verify Stock Increase
        $finalStock = \App\Modules\Inventory\Models\StockLedger::where('item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->sum('quantity_available');

        $this->assertEquals($initialStock + 100, $finalStock, "Stock should have increased by 100");

        // Verify PO Status Completed
        $po->refresh();
        $this->assertEquals(PurchaseOrder::STATUS_COMPLETED, $po->status);
    }
}
