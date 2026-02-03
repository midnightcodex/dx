<?php

namespace Database\Seeders;

use App\Modules\Inventory\Models\Item;
use App\Modules\Shared\Models\ItemCategory;
use App\Modules\Shared\Models\Organization;
use App\Modules\Shared\Models\Uom;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Seed sample items for testing Work Orders.
     */
    public function run(): void
    {
        $org = Organization::where('code', 'DEMO')->firstOrFail();

        // Get UOMs and Categories
        $eachUom = Uom::where('symbol', 'EA')->where('organization_id', $org->id)->firstOrFail();
        $kgUom = Uom::where('symbol', 'KG')->where('organization_id', $org->id)->firstOrFail();
        $meterUom = Uom::where('symbol', 'M')->where('organization_id', $org->id)->firstOrFail();

        $rawCategory = ItemCategory::where('code', 'RAW')->where('organization_id', $org->id)->firstOrFail();
        $fgCategory = ItemCategory::where('code', 'FG')->where('organization_id', $org->id)->firstOrFail();

        // ===== RAW MATERIALS =====
        $rawMaterials = [
            [
                'item_code' => 'RM-STEEL-001',
                'name' => 'Steel Sheet 2mm',
                'description' => 'Cold rolled steel sheet, 2mm thickness',
                'primary_uom_id' => $kgUom->id,
                'category_id' => $rawCategory->id,
                'standard_cost' => 85.00,
                'reorder_level' => 100,
            ],
            [
                'item_code' => 'RM-ALUM-001',
                'name' => 'Aluminum Bar 10mm',
                'description' => 'Aluminum bar stock, 10mm diameter',
                'primary_uom_id' => $meterUom->id,
                'category_id' => $rawCategory->id,
                'standard_cost' => 120.00,
                'reorder_level' => 50,
            ],
            [
                'item_code' => 'RM-BOLT-001',
                'name' => 'Hex Bolt M8x25',
                'description' => 'Stainless steel hex bolt, M8 x 25mm',
                'primary_uom_id' => $eachUom->id,
                'category_id' => $rawCategory->id,
                'standard_cost' => 2.50,
                'reorder_level' => 500,
            ],
            [
                'item_code' => 'RM-NUT-001',
                'name' => 'Hex Nut M8',
                'description' => 'Stainless steel hex nut, M8',
                'primary_uom_id' => $eachUom->id,
                'category_id' => $rawCategory->id,
                'standard_cost' => 0.80,
                'reorder_level' => 500,
            ],
            [
                'item_code' => 'RM-PAINT-001',
                'name' => 'Industrial Paint - Blue',
                'description' => 'Industrial grade enamel paint, blue',
                'primary_uom_id' => $kgUom->id,
                'category_id' => $rawCategory->id,
                'standard_cost' => 350.00,
                'reorder_level' => 20,
            ],
        ];

        foreach ($rawMaterials as $item) {
            Item::firstOrCreate(
                ['organization_id' => $org->id, 'item_code' => $item['item_code']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'primary_uom_id' => $item['primary_uom_id'],
                    'category_id' => $item['category_id'],
                    'item_type' => 'RAW_MATERIAL',
                    'standard_cost' => $item['standard_cost'],
                    'reorder_level' => $item['reorder_level'],
                    'is_active' => true,
                ]
            );
        }
        $this->command->info('Created Raw Materials: ' . count($rawMaterials) . ' items');

        // ===== FINISHED GOODS =====
        $finishedGoods = [
            [
                'item_code' => 'FG-BRACKET-A1',
                'name' => 'Steel Bracket A1',
                'description' => 'Heavy duty steel mounting bracket, Type A1',
                'primary_uom_id' => $eachUom->id,
                'category_id' => $fgCategory->id,
                'standard_cost' => 45.00,
                'reorder_level' => 50,
            ],
            [
                'item_code' => 'FG-FRAME-B2',
                'name' => 'Aluminum Frame B2',
                'description' => 'Lightweight aluminum frame assembly, Model B2',
                'primary_uom_id' => $eachUom->id,
                'category_id' => $fgCategory->id,
                'standard_cost' => 280.00,
                'reorder_level' => 25,
            ],
            [
                'item_code' => 'FG-PANEL-C3',
                'name' => 'Control Panel C3',
                'description' => 'Pre-assembled control panel with mounting hardware',
                'primary_uom_id' => $eachUom->id,
                'category_id' => $fgCategory->id,
                'standard_cost' => 550.00,
                'reorder_level' => 10,
            ],
        ];

        foreach ($finishedGoods as $item) {
            Item::firstOrCreate(
                ['organization_id' => $org->id, 'item_code' => $item['item_code']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'primary_uom_id' => $item['primary_uom_id'],
                    'category_id' => $item['category_id'],
                    'item_type' => 'FINISHED_GOOD',
                    'standard_cost' => $item['standard_cost'],
                    'reorder_level' => $item['reorder_level'],
                    'is_active' => true,
                ]
            );
        }
        $this->command->info('Created Finished Goods: ' . count($finishedGoods) . ' items');
    }
}
