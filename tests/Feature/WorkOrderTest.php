<?php

namespace Tests\Feature;

use Tests\TestCase;

class WorkOrderTest extends TestCase
{
    public function test_work_order_web_page_requires_authentication(): void
    {
        $this->get('/manufacturing/work-orders')->assertRedirect('/login');
    }

    public function test_work_order_api_requires_authentication(): void
    {
        $payload = [
            'item_id' => '00000000-0000-0000-0000-000000000000',
            'bom_id' => '00000000-0000-0000-0000-000000000000',
            'planned_quantity' => 10,
            'scheduled_start_date' => now()->toDateString(),
            'source_warehouse_id' => '00000000-0000-0000-0000-000000000000',
            'target_warehouse_id' => '00000000-0000-0000-0000-000000000000',
        ];

        $this->postJson('/api/manufacturing/work-orders', $payload)->assertStatus(401);
    }
}
