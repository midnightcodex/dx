<?php

namespace Database\Seeders;

use App\Modules\Manufacturing\Models\BomHeader;
use App\Modules\Manufacturing\Models\BomLine;
use App\Modules\Inventory\Models\Item;
use App\Modules\Shared\Models\Organization;
use Illuminate\Database\Seeder;

class ManufacturingSeeder extends Seeder
{
    /**
     * Seed BOMs for the finished goods.
     */
    public function run(): void
    {
        $org = Organization::where('code', 'DEMO')->firstOrFail();

        // Get items by code
        $steelSheet = Item::where('item_code', 'RM-STEEL-001')->where('organization_id', $org->id)->first();
        $aluminumBar = Item::where('item_code', 'RM-ALUM-001')->where('organization_id', $org->id)->first();
        $hexBolt = Item::where('item_code', 'RM-BOLT-001')->where('organization_id', $org->id)->first();
        $hexNut = Item::where('item_code', 'RM-NUT-001')->where('organization_id', $org->id)->first();
        $paint = Item::where('item_code', 'RM-PAINT-001')->where('organization_id', $org->id)->first();

        $bracketA1 = Item::where('item_code', 'FG-BRACKET-A1')->where('organization_id', $org->id)->first();
        $frameB2 = Item::where('item_code', 'FG-FRAME-B2')->where('organization_id', $org->id)->first();
        $panelC3 = Item::where('item_code', 'FG-PANEL-C3')->where('organization_id', $org->id)->first();

        if (!$bracketA1 || !$steelSheet) {
            $this->command->error('Items not found! Run ItemSeeder first.');
            return;
        }

        // ===== BOM 1: Steel Bracket A1 =====
        $bom1 = BomHeader::firstOrCreate(
            ['organization_id' => $org->id, 'bom_number' => 'BOM-BRACKET-A1'],
            [
                'item_id' => $bracketA1->id,
                'version' => 1,
                'notes' => 'Bill of Materials for Steel Bracket A1',
                'base_quantity' => 1,
                'is_active' => true,
            ]
        );

        if ($bom1->wasRecentlyCreated) {
            $bomLines1 = [
                ['item_id' => $steelSheet->id, 'quantity' => 0.5, 'sequence' => 10], // 0.5 kg steel per bracket
                ['item_id' => $hexBolt->id, 'quantity' => 4, 'sequence' => 20], // 4 bolts
                ['item_id' => $hexNut->id, 'quantity' => 4, 'sequence' => 30], // 4 nuts
            ];

            foreach ($bomLines1 as $index => $line) {
                BomLine::create([
                    'organization_id' => $org->id,
                    'bom_header_id' => $bom1->id,
                    'line_number' => $index + 1,
                    'component_item_id' => $line['item_id'],
                    'quantity_per_unit' => $line['quantity'],
                    'operation_sequence' => $line['sequence'],
                    'scrap_percentage' => 0,
                ]);
            }
            $this->command->info("Created BOM: {$bom1->bom_number}");
        } else {
            $this->command->info("BOM already exists: {$bom1->bom_number}");
        }

        // ===== BOM 2: Aluminum Frame B2 =====
        if ($frameB2 && $aluminumBar) {
            $bom2 = BomHeader::firstOrCreate(
                ['organization_id' => $org->id, 'bom_number' => 'BOM-FRAME-B2'],
                [
                    'item_id' => $frameB2->id,
                    'version' => 1,
                    'notes' => 'Bill of Materials for Aluminum Frame B2',
                    'base_quantity' => 1,
                    'is_active' => true,
                ]
            );

            if ($bom2->wasRecentlyCreated) {
                $bomLines2 = [
                    ['item_id' => $aluminumBar->id, 'quantity' => 2.5, 'sequence' => 10], // 2.5m aluminum bar
                    ['item_id' => $hexBolt->id, 'quantity' => 8, 'sequence' => 20], // 8 bolts
                    ['item_id' => $hexNut->id, 'quantity' => 8, 'sequence' => 30], // 8 nuts
                    ['item_id' => $paint->id, 'quantity' => 0.1, 'sequence' => 40], // 100g paint
                ];

                foreach ($bomLines2 as $index => $line) {
                    BomLine::create([
                        'organization_id' => $org->id,
                        'bom_header_id' => $bom2->id,
                        'line_number' => $index + 1,
                        'component_item_id' => $line['item_id'],
                        'quantity_per_unit' => $line['quantity'],
                        'operation_sequence' => $line['sequence'],
                        'scrap_percentage' => 0,
                    ]);
                }
                $this->command->info("Created BOM: {$bom2->bom_number}");
            } else {
                $this->command->info("BOM already exists: {$bom2->bom_number}");
            }
        }

        // ===== BOM 3: Control Panel C3 (uses Frame B2 as sub-assembly) =====
        if ($panelC3 && $frameB2) {
            $bom3 = BomHeader::firstOrCreate(
                ['organization_id' => $org->id, 'bom_number' => 'BOM-PANEL-C3'],
                [
                    'item_id' => $panelC3->id,
                    'version' => 1,
                    'notes' => 'Bill of Materials for Control Panel C3 (multi-level)',
                    'base_quantity' => 1,
                    'is_active' => true,
                ]
            );

            if ($bom3->wasRecentlyCreated) {
                $bomLines3 = [
                    ['item_id' => $frameB2->id, 'quantity' => 1, 'sequence' => 10], // 1 Frame B2 (sub-assembly)
                    ['item_id' => $steelSheet->id, 'quantity' => 1.5, 'sequence' => 20], // 1.5 kg steel for panel
                    ['item_id' => $hexBolt->id, 'quantity' => 12, 'sequence' => 30], // 12 bolts
                    ['item_id' => $hexNut->id, 'quantity' => 12, 'sequence' => 40], // 12 nuts
                ];

                foreach ($bomLines3 as $index => $line) {
                    BomLine::create([
                        'organization_id' => $org->id,
                        'bom_header_id' => $bom3->id,
                        'line_number' => $index + 1,
                        'component_item_id' => $line['item_id'],
                        'quantity_per_unit' => $line['quantity'],
                        'operation_sequence' => $line['sequence'],
                        'scrap_percentage' => 0,
                    ]);
                }
                $this->command->info("Created BOM: {$bom3->bom_number}");
            } else {
                $this->command->info("BOM already exists: {$bom3->bom_number}");
            }
        }
        $this->command->info('Manufacturing seed complete!');
    }
}
