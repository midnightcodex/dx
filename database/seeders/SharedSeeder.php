<?php

namespace Database\Seeders;

use App\Modules\Shared\Models\ItemCategory;
use App\Modules\Shared\Models\NumberSeries;
use App\Modules\Shared\Models\Organization;
use App\Modules\Shared\Models\Uom;
use Illuminate\Database\Seeder;

class SharedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the demo organization created in AuthSeeder
        $org = Organization::where('code', 'DEMO')->firstOrFail();

        // 1. Units of Measure
        $uoms = [
            ['symbol' => 'EA', 'name' => 'Each', 'category' => 'COUNT'],
            ['symbol' => 'KG', 'name' => 'Kilogram', 'category' => 'WEIGHT'],
            ['symbol' => 'G', 'name' => 'Gram', 'category' => 'WEIGHT'],
            ['symbol' => 'M', 'name' => 'Meter', 'category' => 'LENGTH'],
            ['symbol' => 'CM', 'name' => 'Centimeter', 'category' => 'LENGTH'],
            ['symbol' => 'L', 'name' => 'Liter', 'category' => 'VOLUME'],
            ['symbol' => 'ML', 'name' => 'Milliliter', 'category' => 'VOLUME'],
            ['symbol' => 'BOX', 'name' => 'Box', 'category' => 'COUNT'],
            ['symbol' => 'SET', 'name' => 'Set', 'category' => 'COUNT'],
        ];

        foreach ($uoms as $uom) {
            Uom::firstOrCreate(
                ['organization_id' => $org->id, 'symbol' => $uom['symbol']],
                [
                    'name' => $uom['name'],
                    'category' => $uom['category'],
                    'is_active' => true,
                    'conversion_factor' => 1,
                ]
            );
        }
        $this->command->info('Created/Checked UOMs');

        // 2. Item Categories
        $categories = [
            ['code' => 'RAW', 'name' => 'Raw Materials', 'description' => 'Unprocessed materials used in production'],
            ['code' => 'WIP', 'name' => 'Work in Progress', 'description' => 'Semi-finished goods'],
            ['code' => 'FG', 'name' => 'Finished Goods', 'description' => 'Completed products ready for sale'],
            ['code' => 'CONS', 'name' => 'Consumables', 'description' => 'Items consumed but not part of BOM'],
            ['code' => 'SPARE', 'name' => 'Spare Parts', 'description' => 'Maintenance spares'],
        ];

        foreach ($categories as $cat) {
            ItemCategory::firstOrCreate(
                ['organization_id' => $org->id, 'code' => $cat['code']],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'parent_id' => null, // Added explicit default
                    'type' => 'GENERAL', // Added explicit default
                    'is_active' => true,
                ]
            );
        }
        $this->command->info('Created Item Categories');

        // 3. Number Series (Auto-incrementing numbers for documents)
        $series = [
            ['entity_type' => 'PURCHASE_ORDER', 'prefix' => 'PO-', 'current_number' => 1000, 'padding' => 4],
            ['entity_type' => 'GRN', 'prefix' => 'GRN-', 'current_number' => 1000, 'padding' => 4],
            ['entity_type' => 'WORK_ORDER', 'prefix' => 'WO-', 'current_number' => 1000, 'padding' => 4],
            ['entity_type' => 'ITEM', 'prefix' => 'ITM-', 'current_number' => 1000, 'padding' => 6],
            ['entity_type' => 'BATCH', 'prefix' => 'BAT-', 'current_number' => 1000, 'padding' => 6],
            ['entity_type' => 'TRANSACTION', 'prefix' => 'TRN-', 'current_number' => 1000, 'padding' => 8],
        ];

        foreach ($series as $s) {
            NumberSeries::firstOrCreate(
                ['organization_id' => $org->id, 'entity_type' => $s['entity_type']],
                [
                    'prefix' => $s['prefix'],
                    'current_number' => $s['current_number'],
                    'padding' => $s['padding'],
                    'format' => '{PREFIX}{YYMMDD}-{NNNNNN}',
                ]
            );
        }
        $this->command->info('Created Number Series');
    }
}
