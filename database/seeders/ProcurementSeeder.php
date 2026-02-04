<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Procurement\Models\Vendor;
use App\Modules\Procurement\Models\PurchaseOrder;
use App\Modules\Procurement\Models\PurchaseOrderLine;
use App\Modules\Shared\Models\Organization;
use App\Modules\Inventory\Models\Item;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Auth\Models\User;

class ProcurementSeeder extends Seeder
{
    /**
     * Seed the procurement module with vendors and sample purchase orders.
     */
    public function run(): void
    {
        $org = Organization::first();
        $admin = User::where('email', 'admin@examp.com')->first();
        $warehouse = Warehouse::where('code', 'WH-MAIN')->first();

        if (!$org || !$admin || !$warehouse) {
            $this->command->warn('Skipping ProcurementSeeder: Missing org, admin, or warehouse. Run other seeders first.');
            return;
        }

        // ─────────────────────────────────────────────────────────────
        // VENDORS
        // ─────────────────────────────────────────────────────────────
        $vendors = [
            [
                'vendor_code' => 'V-STEEL',
                'name' => 'Steel Corp Ltd',
                'contact_person' => 'Rajesh Kumar',
                'email' => 'sales@steelcorp.com',
                'phone' => '+91-9876543210',
                'address' => '123 Industrial Area, Sector 5',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'country' => 'India',
                'postal_code' => '400001',
                'tax_id' => 'GSTIN27AABCS1234A1Z5',
                'payment_terms' => 'NET30',
                'currency' => 'INR',
                'credit_limit' => 500000,
                'status' => 'ACTIVE',
                'notes' => 'Primary steel supplier. Quality certified.',
            ],
            [
                'vendor_code' => 'V-ALUM',
                'name' => 'Aluminum Traders',
                'contact_person' => 'Suresh Patel',
                'email' => 'orders@alumtraders.in',
                'phone' => '+91-9123456789',
                'address' => '45 Metal Market, GIDC',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'country' => 'India',
                'postal_code' => '380015',
                'tax_id' => 'GSTIN24AABCA5678B1Z2',
                'payment_terms' => 'NET60',
                'currency' => 'INR',
                'credit_limit' => 300000,
                'status' => 'ACTIVE',
                'notes' => 'Aluminum sheets and extrusions supplier.',
            ],
            [
                'vendor_code' => 'V-FAST',
                'name' => 'Fasteners & Hardware Co',
                'contact_person' => 'Amit Shah',
                'email' => 'sales@fastenerco.com',
                'phone' => '+91-9988776655',
                'address' => '78 Hardware Lane',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'country' => 'India',
                'postal_code' => '110001',
                'tax_id' => 'GSTIN07AABCF9012C1Z8',
                'payment_terms' => 'NET30',
                'currency' => 'INR',
                'credit_limit' => 100000,
                'status' => 'ACTIVE',
                'notes' => 'Bolts, nuts, screws supplier.',
            ],
        ];

        $createdVendors = [];
        foreach ($vendors as $vendorData) {
            $vendor = Vendor::firstOrCreate(
                ['organization_id' => $org->id, 'vendor_code' => $vendorData['vendor_code']],
                array_merge($vendorData, [
                    'organization_id' => $org->id,
                    'created_by' => $admin->id,
                ])
            );
            $createdVendors[$vendorData['vendor_code']] = $vendor;

            if ($vendor->wasRecentlyCreated) {
                $this->command->info("Created vendor: {$vendor->name}");
            }
        }

        // ─────────────────────────────────────────────────────────────
        // SAMPLE PURCHASE ORDER (APPROVED, ready for GRN)
        // ─────────────────────────────────────────────────────────────
        $steelVendor = $createdVendors['V-STEEL'];

        // Get raw material items to order
        $steelSheet = Item::where('item_code', 'RM-STEEL-2MM')->first();
        $bolt = Item::where('item_code', 'RM-BOLT-M8')->first();

        if (!$steelSheet || !$bolt) {
            $this->command->warn('Skipping sample PO: Raw material items not found.');
            return;
        }

        $poNumber = 'PO-' . now()->format('Ym') . '-0001';

        $po = PurchaseOrder::firstOrCreate(
            ['organization_id' => $org->id, 'po_number' => $poNumber],
            [
                'organization_id' => $org->id,
                'po_number' => $poNumber,
                'vendor_id' => $steelVendor->id,
                'order_date' => now()->subDays(3),
                'expected_date' => now()->addDays(4),
                'delivery_warehouse_id' => $warehouse->id,
                'status' => PurchaseOrder::STATUS_APPROVED,
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'currency' => 'INR',
                'payment_terms' => 'NET30',
                'notes' => 'Sample approved PO for testing GRN flow.',
                'created_by' => $admin->id,
                'approved_by' => $admin->id,
                'approved_at' => now()->subDays(2),
            ]
        );

        if ($po->wasRecentlyCreated) {
            $this->command->info("Created sample PO: {$po->po_number}");

            // Add PO Lines
            $lines = [
                [
                    'item_id' => $steelSheet->id,
                    'description' => 'Steel Sheet 2mm for bracket production',
                    'quantity' => 100,
                    'unit_price' => 250.00,
                    'tax_rate' => 18,
                ],
                [
                    'item_id' => $bolt->id,
                    'description' => 'M8 Bolts for assembly',
                    'quantity' => 500,
                    'unit_price' => 5.50,
                    'tax_rate' => 18,
                ],
            ];

            $lineNumber = 1;
            foreach ($lines as $lineData) {
                $baseAmount = $lineData['quantity'] * $lineData['unit_price'];
                $taxAmount = $baseAmount * ($lineData['tax_rate'] / 100);
                $lineAmount = $baseAmount + $taxAmount;

                PurchaseOrderLine::create([
                    'organization_id' => $org->id,
                    'purchase_order_id' => $po->id,
                    'line_number' => $lineNumber++,
                    'item_id' => $lineData['item_id'],
                    'description' => $lineData['description'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'tax_rate' => $lineData['tax_rate'],
                    'tax_amount' => $taxAmount,
                    'line_amount' => $lineAmount,
                    'received_quantity' => 0,
                ]);
            }

            // Recalculate totals
            $po->refresh();
            $po->calculateTotals();
            $po->save();

            $this->command->info("Added {$lineNumber} lines to PO. Total: ₹{$po->total_amount}");
        }
    }
}
