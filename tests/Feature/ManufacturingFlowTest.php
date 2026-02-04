<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Auth\Models\User;
use App\Modules\Inventory\Models\Item;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Manufacturing\Models\WorkOrder;
use App\Modules\Manufacturing\Models\WorkOrderMaterial;

class ManufacturingFlowTest extends TestCase
{
    // Reliance on seeded data

    public function test_manufacturing_execution_flow()
    {
        // 1. Setup
        $user = User::where('email', 'admin@examp.com')->first();
        $this->actingAs($user);

        // Get a Finished Good with BOM (from seed)
        // Ensure we pick one that has BOM headers
        $fgItem = Item::whereHas('bomHeaders')->firstOrFail();
        $sourceWh = Warehouse::where('code', 'WH-MAIN')->firstOrFail(); // Raw materials here
        $targetWh = Warehouse::where('code', 'WH-PROD')->firstOrFail(); // Finished goods here

        // 2. Create WO
        $payload = [
            'item_id' => $fgItem->id,
            'bom_id' => $fgItem->bomHeaders->first()->id,
            'planned_quantity' => 10,
            'scheduled_start_date' => now()->format('Y-m-d'),
            'source_warehouse_id' => $sourceWh->id,
            'target_warehouse_id' => $targetWh->id,
        ];

        $response = $this->post(route('manufacturing.work-orders.store'), $payload);
        $response->assertRedirect();

        $wo = WorkOrder::latest()->first();
        $this->assertEquals(WorkOrder::STATUS_PLANNED, $wo->status);

        // 3. Release WO (Explode BOM)
        $this->post(route('manufacturing.work-orders.release', $wo->id));
        $wo->refresh();
        $this->assertEquals(WorkOrder::STATUS_RELEASED, $wo->status);
        $this->assertNotEmpty($wo->materials); // Materials should be created

        // 4. Start Production
        $this->post(route('manufacturing.work-orders.start', $wo->id));
        $wo->refresh();
        $this->assertEquals(WorkOrder::STATUS_IN_PROGRESS, $wo->status);

        // 5. Issue Materials
        $material = $wo->materials->first();
        $issuePayload = [
            'materials' => [
                [
                    'id' => $material->id,
                    'quantity' => $material->required_quantity, // Issue full amount
                ]
            ]
        ];

        // Check stock before issue (Optional, relying on successful post)

        $this->post(route('manufacturing.work-orders.issue-materials', $wo->id), $issuePayload);

        $material->refresh();
        $this->assertEquals($material->required_quantity, $material->issued_quantity);

        // 6. Complete Production (Record Output)
        $completePayload = [
            'completed_quantity' => 10,
            'rejected_quantity' => 0,
            'notes' => 'All good',
        ];

        // Store initial FG Stock
        $initialFgStock = \App\Modules\Inventory\Models\StockLedger::where('item_id', $fgItem->id)
            ->where('warehouse_id', $targetWh->id)
            ->sum('quantity_available');

        $this->post(route('manufacturing.work-orders.complete', $wo->id), $completePayload);

        $wo->refresh();
        $this->assertEquals(WorkOrder::STATUS_COMPLETED, $wo->status);
        $this->assertEquals(10, $wo->completed_quantity);

        // Verify FG Stock Increased
        $finalFgStock = \App\Modules\Inventory\Models\StockLedger::where('item_id', $fgItem->id)
            ->where('warehouse_id', $targetWh->id)
            ->sum('quantity_available');

        $this->assertEquals($initialFgStock + 10, $finalFgStock);
    }
}
