<?php

namespace Database\Seeders;

use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Shared\Models\Organization;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $org = Organization::where('code', 'DEMO')->firstOrFail();

        $warehouses = [
            [
                'name' => 'Main Warehouse',
                'code' => 'MAIN',
                'type' => 'WAREHOUSE',
                'address' => 'Building A, Industrial Park',
                'allow_negative_stock' => false,
            ],
            [
                'name' => 'Shop Floor (WIP)',
                'code' => 'SHOP',
                'type' => 'SHOP_FLOOR',
                'address' => 'Building B, Production Area',
                'allow_negative_stock' => true, // Allowed for WIP consumption timing issues
            ],
            [
                'name' => 'Quarantine Area',
                'code' => 'QUAR',
                'type' => 'QUARANTINE',
                'address' => 'Building A, Secure Cage',
                'allow_negative_stock' => false,
            ],
        ];

        foreach ($warehouses as $wh) {
            Warehouse::firstOrCreate(
                ['organization_id' => $org->id, 'code' => $wh['code']],
                [
                    'name' => $wh['name'],
                    'type' => $wh['type'],
                    'address' => $wh['address'],
                    'allow_negative_stock' => $wh['allow_negative_stock'],
                    'is_active' => true,
                ]
            );
        }
        $this->command->info('Created Warehouses');
    }
}
