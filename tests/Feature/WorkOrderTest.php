<?php

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Inventory\Models\Item;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Manufacturing\Models\BomHeader;
use App\Modules\Manufacturing\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderTest extends TestCase
{
    // We don't use RefreshDatabase here because we want to use the seeded data
    // typically in a real CI/CD we would seed a test DB, but here we are testing against the local dev DB
    // or we can just use transactions if we want to be safe, but let's just create one and cleanup or leave it.

    public function test_admin_can_create_work_order()
    {
        // 1. Login as Admin
        $user = User::where('email', 'admin@examp.com')->firstOrFail();
        $this->actingAs($user);

        // 2. Get necessary data (seeded)
        $item = Item::where('item_code', 'FG-BRACKET-A1')->firstOrFail();
        $bom = BomHeader::where('bom_number', 'BOM-BRACKET-A1')->firstOrFail();
        $warehouse = Warehouse::where('code', 'WH-MAIN')->firstOrFail();

        // 3. Define payload
        $payload = [
            'item_id' => $item->id,
            'bom_id' => $bom->id,
            'planned_quantity' => 10,
            'scheduled_start_date' => now()->addDay()->format('Y-m-d'),
            'scheduled_end_date' => now()->addDays(2)->format('Y-m-d'),
            'source_warehouse_id' => $warehouse->id,
            'target_warehouse_id' => $warehouse->id,
            'priority' => 'HIGH',
            'notes' => 'Test Work Order from Automated Test',
        ];

        // 4. Send POST request
        $response = $this->post('/manufacturing/work-orders', $payload);

        // 5. Verify Redirect
        $response->assertRedirect('/manufacturing/work-orders');
        $response->assertSessionHas('message');

        // 6. Verify Database
        $this->assertDatabaseHas('manufacturing.work_orders', [
            'item_id' => $item->id,
            'planned_quantity' => 10,
            'status' => 'PLANNED',
            'notes' => 'Test Work Order from Automated Test',
        ]);
    }
}
