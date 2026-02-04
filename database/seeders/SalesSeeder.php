<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Sales\Models\Customer;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Models\SalesOrderLine;
use App\Modules\Inventory\Models\Item;
use App\Modules\Inventory\Models\Warehouse;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $organizationId = 'org-0000-0000-0000-000000000001'; // Default Org
        $commonFields = [
            'organization_id' => $organizationId,
            'created_by' => 'user-0000-0000-0000-000000000001',
        ];

        // 1. Create Customers
        $customers = [
            [
                'name' => 'TechSolutions Inc',
                'email' => 'purchasing@techsolutions.com',
                'phone' => '123-555-0101',
                'payment_terms' => 'NET30',
                'billing_address' => '123 Tech Park, Silicon Valley, CA',
                'shipping_address' => 'Warehouse 4, 123 Tech Park, Silicon Valley, CA',
            ],
            [
                'name' => 'Global Retailers Ltd',
                'email' => 'orders@globalretail.com',
                'phone' => '123-555-0102',
                'payment_terms' => 'NET60',
                'billing_address' => '456 Market St, New York, NY',
                'shipping_address' => 'Distribution Center, Jersey City, NJ',
            ],
            [
                'name' => 'John Doe (B2C)',
                'email' => 'john.doe@email.com',
                'phone' => '555-0199',
                'payment_terms' => 'COD',
                'billing_address' => '789 Maple Ave, Smalltown, OH',
                'shipping_address' => '789 Maple Ave, Smalltown, OH',
            ]
        ];

        foreach ($customers as $custData) {
            Customer::firstOrCreate(
                ['name' => $custData['name']],
                array_merge($custData, $commonFields)
            );
        }

        // 2. Create a Sample Sales Order (Confirmed)
        $customer = Customer::where('name', 'TechSolutions Inc')->first();
        $item = Item::first(); // Grab any item (e.g. Steel or Widget)

        if ($customer && $item) {
            $so = SalesOrder::firstOrCreate(
                ['so_number' => 'SO-202602-0001'],
                array_merge($commonFields, [
                    'customer_id' => $customer->id,
                    'order_date' => now(),
                    'expected_ship_date' => now()->addDays(3),
                    'status' => 'CONFIRMED',
                    'subtotal' => 500.00,
                    'tax_amount' => 50.00,
                    'total_amount' => 550.00,
                    'billing_address_snapshot' => $customer->billing_address,
                    'shipping_address_snapshot' => $customer->shipping_address,
                ])
            );

            // Lines
            SalesOrderLine::firstOrCreate(
                ['sales_order_id' => $so->id, 'item_id' => $item->id],
                array_merge($commonFields, [
                    'quantity' => 10,
                    'unit_price' => 50.00,
                    'tax_rate' => 10.00,
                    'line_amount' => 550.00,
                    'shipped_quantity' => 0,
                ])
            );
        }
    }
}
