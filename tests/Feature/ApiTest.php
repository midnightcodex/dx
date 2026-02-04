<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Auth\Models\User;
use App\Modules\Inventory\Models\Item;
use App\Modules\Sales\Models\Customer;

class ApiTest extends TestCase
{
    public function test_api_login_and_access()
    {
        // 1. Setup
        $user = User::where('email', 'admin@examp.com')->first();
        if (!$user) { // Fallback for fresh db run
            $user = User::factory()->create(['email' => 'admin@examp.com', 'password' => bcrypt('password')]);
        }

        // 2. Login
        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password', // Assumes seeder sets this
            'device_name' => 'TestDevice'
        ]);

        $response->assertStatus(200);
        $token = $response->json('token');
        $this->assertNotNull($token);

        // 3. Access Protected Route (Inventory)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/inventory/items');

        $response->assertStatus(200);
    }

    public function test_api_create_sales_order()
    {
        $user = User::where('email', 'admin@examp.com')->first();
        $token = $user->createToken('test')->plainTextToken;
        $orgId = $user->organization_id;

        // Setup Data
        $customer = Customer::first() ?? Customer::create(['organization_id' => $orgId, 'name' => 'API Customer', 'email' => 'api@test.com']);
        $item = Item::first() ?? Item::create(['organization_id' => $orgId, 'name' => 'API Item', 'sku' => 'API-001', 'sale_price' => 100]);

        // Payload
        $payload = [
            'customer_id' => $customer->id,
            'order_date' => date('Y-m-d'),
            'lines' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 5,
                    'unit_price' => 100
                ]
            ]
        ];

        // Call API
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/sales/orders', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('sales.sales_orders', ['so_number' => $response->json('so_number')]);
    }
}
