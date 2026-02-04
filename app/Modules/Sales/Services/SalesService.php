<?php

namespace App\Modules\Sales\Services;

use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Models\SalesOrderLine;
use App\Modules\Sales\Models\Shipment;
use App\Modules\Sales\Models\ShipmentLine;
use App\Modules\Inventory\Services\InventoryPostingService;
use App\Modules\Sales\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesService
{
    protected InventoryPostingService $inventoryService;

    public function __construct(InventoryPostingService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function createOrder(array $data, int $userId, string $orgId): SalesOrder
    {
        return DB::transaction(function () use ($data, $userId, $orgId) {
            $customer = Customer::findOrFail($data['customer_id']);
            $soNumber = $this->generateSoNumber();

            $so = SalesOrder::create([
                'organization_id' => $orgId,
                'so_number' => $soNumber,
                'customer_id' => $data['customer_id'],
                'order_date' => $data['order_date'],
                'expected_ship_date' => $data['expected_ship_date'] ?? null,
                'status' => 'DRAFT',
                'created_by' => $userId,
                'billing_address_snapshot' => $customer->billing_address,
                'shipping_address_snapshot' => $customer->shipping_address,
                'notes' => $data['notes'] ?? null,
            ]);

            $subtotal = 0;
            $taxTotal = 0;

            foreach ($data['lines'] as $lineData) {
                $qty = $lineData['quantity'];
                $price = $lineData['unit_price'];
                $taxRate = $lineData['tax_rate'] ?? 0;

                $lineSubtotal = $qty * $price;
                $lineTax = $lineSubtotal * ($taxRate / 100);

                SalesOrderLine::create([
                    'organization_id' => $orgId,
                    'sales_order_id' => $so->id,
                    'item_id' => $lineData['item_id'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => $taxRate,
                    'line_amount' => $lineSubtotal,
                ]);

                $subtotal += $lineSubtotal;
                $taxTotal += $lineTax;
            }

            $so->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxTotal,
                'total_amount' => $subtotal + $taxTotal,
            ]);

            return $so;
        });
    }

    public function shipOrder(SalesOrder $so, string $warehouseId, string $shipDate, array $linesToShip, int $userId): Shipment
    {
        return DB::transaction(function () use ($so, $warehouseId, $shipDate, $linesToShip, $userId) {

            $shipment = Shipment::create([
                'organization_id' => $so->organization_id,
                'shipment_number' => 'SH-' . strtoupper(uniqid()),
                'sales_order_id' => $so->id,
                'warehouse_id' => $warehouseId,
                'shipment_date' => $shipDate,
                'status' => 'SHIPPED',
                'created_by' => $userId,
            ]);

            foreach ($linesToShip as $shipLine) {
                $soLine = $so->lines->where('id', $shipLine['line_id'])->first();
                if (!$soLine)
                    continue;

                $qtyToShip = $shipLine['quantity'];

                // Deduct Inventory
                try {
                    $this->inventoryService->post(
                        transactionType: 'ISSUE',
                        itemId: $soLine->item_id,
                        warehouseId: $warehouseId,
                        quantity: -$qtyToShip,
                        unitCost: 0.0,
                        referenceType: 'SHIPMENT',
                        referenceId: $shipment->id,
                        organizationId: $so->organization_id
                    );
                } catch (\Exception $e) {
                    throw ValidationException::withMessages(['inventory' => "Inventory error for item {$soLine->item_id}: " . $e->getMessage()]);
                }

                ShipmentLine::create([
                    'organization_id' => $so->organization_id,
                    'shipment_id' => $shipment->id,
                    'sales_order_line_id' => $soLine->id,
                    'item_id' => $soLine->item_id,
                    'quantity' => $qtyToShip,
                ]);

                $soLine->increment('shipped_quantity', $qtyToShip);
            }

            // Check if fully shipped
            $allShipped = $so->lines->every(fn($l) => $l->fresh()->shipped_quantity >= $l->quantity);
            if ($allShipped) {
                $so->update(['status' => 'SHIPPED']);
            }

            return $shipment;
        });
    }

    private function generateSoNumber()
    {
        $prefix = 'SO-' . date('Ym');
        $last = SalesOrder::where('so_number', 'like', "$prefix%")->count();
        return $prefix . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }
}
