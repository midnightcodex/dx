<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\Item;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Models\StockLedger;
use App\Modules\Inventory\Models\StockTransaction;
use App\Modules\Inventory\Models\Batch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * InventoryPostingService - THE SINGLE ENTRY POINT FOR ALL INVENTORY CHANGES.
 * 
 * CRITICAL ARCHITECTURE RULE (from docs.md Section 3.2):
 * - This is the ONLY class that can write to stock_ledger or stock_transactions
 * - No other code should touch these tables directly
 * - All inventory movements MUST go through this service
 * 
 * Transaction Flow:
 * 1. Validate business rules
 * 2. Create immutable transaction (THE FACT)
 * 3. Update derived ledger (THE TRUTH)
 * 4. Emit domain event
 * 5. Fail atomically
 */
class InventoryPostingService
{
    /**
     * Post a stock transaction and update the ledger.
     * 
     * @param string $transactionType  'RECEIPT', 'ISSUE', 'ADJUSTMENT', 'TRANSFER'
     * @param string $itemId           The item being transacted
     * @param string $warehouseId      The warehouse location
     * @param float  $quantity         Positive for receipts, negative for issues
     * @param float  $unitCost         Cost per unit
     * @param string $referenceType    Source document type: 'GRN', 'WORK_ORDER', 'SALES_ORDER', etc.
     * @param string $referenceId      Source document ID
     * @param string|null $batchId     Optional batch ID for batch-tracked items
     * @param string|null $organizationId Optional, defaults to auth user's org
     * 
     * @return StockTransaction The created transaction
     * 
     * @throws \Exception If validation fails or negative stock not allowed
     */
    public function post(
        string $transactionType,
        string $itemId,
        string $warehouseId,
        float $quantity,
        float $unitCost,
        string $referenceType,
        string $referenceId,
        ?string $batchId = null,
        ?string $organizationId = null
    ): StockTransaction {

        return DB::transaction(function () use ($transactionType, $itemId, $warehouseId, $quantity, $unitCost, $referenceType, $referenceId, $batchId, $organizationId) {
            $orgId = $organizationId ?? auth()->user()->organization_id;

            // 1. VALIDATE BUSINESS RULES
            $this->validatePosting($itemId, $warehouseId, $quantity, $batchId, $orgId);

            // 2. LOCK AND GET/CREATE LEDGER ENTRY
            $ledger = StockLedger::where('organization_id', $orgId)
                ->where('item_id', $itemId)
                ->where('warehouse_id', $warehouseId)
                ->where('batch_id', $batchId)
                ->lockForUpdate()
                ->first();

            if (!$ledger) {
                $ledger = new StockLedger([
                    'id' => (string) Str::uuid(),
                    'organization_id' => $orgId,
                    'item_id' => $itemId,
                    'warehouse_id' => $warehouseId,
                    'batch_id' => $batchId,
                    'quantity_available' => 0,
                    'quantity_reserved' => 0,
                    'quantity_in_transit' => 0,
                    'unit_cost' => $unitCost,
                ]);
            }

            // 3. CALCULATE NEW BALANCE
            $newQty = (float) $ledger->quantity_available + $quantity;

            // Check negative stock
            $warehouse = Warehouse::find($warehouseId);
            if ($newQty < 0 && !$warehouse->allow_negative_stock) {
                throw new \RuntimeException(
                    "Insufficient stock. Available: {$ledger->quantity_available}, " .
                    "Requested: " . abs($quantity) . ". " .
                    "Warehouse does not allow negative stock."
                );
            }

            // 4. CREATE IMMUTABLE TRANSACTION (THE FACT)
            $transaction = StockTransaction::create([
                'id' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'transaction_type' => $transactionType,
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'batch_id' => $batchId,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_value' => $quantity * $unitCost,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'balance_after' => $newQty,
                'created_by' => auth()->id(),
                'transaction_date' => now(),
            ]);

            // 5. UPDATE LEDGER (THE TRUTH)
            $ledger->quantity_available = $newQty;
            $ledger->last_transaction_id = $transaction->id;
            $ledger->last_updated = now();

            // Update weighted average cost for receipts
            if ($quantity > 0 && $unitCost > 0) {
                $ledger->unit_cost = $this->calculateWeightedAverageCost(
                    (float) $ledger->quantity_available - $quantity,
                    (float) $ledger->unit_cost,
                    $quantity,
                    $unitCost
                );
            }

            $ledger->save();

            // 6. EMIT DOMAIN EVENT (for other modules to react)
            event(new \App\Core\Events\StockPosted($transaction));

            return $transaction;
        });
    }

    /**
     * Cancel a transaction by creating a compensating (reversal) entry.
     * NEVER deletes the original transaction - maintains full audit trail.
     * 
     * @param string $transactionId The transaction to cancel
     * @param string $reason        Reason for cancellation
     * 
     * @return StockTransaction The reversal transaction
     */
    public function cancelTransaction(string $transactionId, string $reason): StockTransaction
    {
        $original = StockTransaction::findOrFail($transactionId);

        if ($original->is_cancelled) {
            throw new \RuntimeException("Transaction is already cancelled.");
        }

        return DB::transaction(function () use ($original, $reason) {
            // Mark original as cancelled (metadata only - not changing the fact)
            DB::table('inventory.stock_transactions')
                ->where('id', $original->id)
                ->update([
                    'is_cancelled' => true,
                    'cancelled_reason' => $reason,
                ]);

            // Create reversal transaction (the compensating entry)
            return $this->post(
                transactionType: $original->transaction_type . '_REVERSAL',
                itemId: $original->item_id,
                warehouseId: $original->warehouse_id,
                quantity: -1 * (float) $original->quantity, // Opposite quantity
                unitCost: (float) $original->unit_cost,
                referenceType: 'CANCELLATION',
                referenceId: $original->id,
                batchId: $original->batch_id,
                organizationId: $original->organization_id
            );
        });
    }

    /**
     * Reserve stock for a future issue (e.g., sales order allocation).
     */
    public function reserveStock(
        string $itemId,
        string $warehouseId,
        float $quantity,
        string $referenceType,
        string $referenceId,
        ?string $batchId = null
    ): void {
        DB::transaction(function () use ($itemId, $warehouseId, $quantity, $batchId) {
            $ledger = StockLedger::where('item_id', $itemId)
                ->where('warehouse_id', $warehouseId)
                ->where('batch_id', $batchId)
                ->lockForUpdate()
                ->firstOrFail();

            $available = (float) $ledger->quantity_available - (float) $ledger->quantity_reserved;

            if ($quantity > $available) {
                throw new \RuntimeException(
                    "Cannot reserve {$quantity}. Only {$available} available for reservation."
                );
            }

            $ledger->quantity_reserved = (float) $ledger->quantity_reserved + $quantity;
            $ledger->save();
        });
    }

    /**
     * Release a stock reservation.
     */
    public function releaseReservation(
        string $itemId,
        string $warehouseId,
        float $quantity,
        ?string $batchId = null
    ): void {
        DB::transaction(function () use ($itemId, $warehouseId, $quantity, $batchId) {
            $ledger = StockLedger::where('item_id', $itemId)
                ->where('warehouse_id', $warehouseId)
                ->where('batch_id', $batchId)
                ->lockForUpdate()
                ->firstOrFail();

            $ledger->quantity_reserved = max(0, (float) $ledger->quantity_reserved - $quantity);
            $ledger->save();
        });
    }

    /**
     * Get current stock for an item in a warehouse.
     */
    public function getStock(string $itemId, string $warehouseId, ?string $batchId = null): array
    {
        $ledger = StockLedger::where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->where('batch_id', $batchId)
            ->first();

        if (!$ledger) {
            return [
                'available' => 0,
                'reserved' => 0,
                'in_transit' => 0,
                'net_available' => 0,
            ];
        }

        return [
            'available' => (float) $ledger->quantity_available,
            'reserved' => (float) $ledger->quantity_reserved,
            'in_transit' => (float) $ledger->quantity_in_transit,
            'net_available' => (float) $ledger->quantity_available - (float) $ledger->quantity_reserved,
        ];
    }

    /**
     * Validate a posting before execution.
     */
    protected function validatePosting(
        string $itemId,
        string $warehouseId,
        float $quantity,
        ?string $batchId,
        string $organizationId
    ): void {
        // Validate item exists and is active
        $item = Item::where('id', $itemId)
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->first();

        if (!$item) {
            throw new \RuntimeException("Item not found or inactive: {$itemId}");
        }

        // Validate warehouse exists and is active
        $warehouse = Warehouse::where('id', $warehouseId)
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->first();

        if (!$warehouse) {
            throw new \RuntimeException("Warehouse not found or inactive: {$warehouseId}");
        }

        // Validate batch if item is batch-tracked
        if ($item->is_batch_tracked && $quantity > 0 && !$batchId) {
            throw new \RuntimeException("Item is batch-tracked. Batch ID required for receipts.");
        }

        if ($batchId) {
            $batch = Batch::where('id', $batchId)
                ->where('item_id', $itemId)
                ->first();

            if (!$batch) {
                throw new \RuntimeException("Batch not found for item: {$batchId}");
            }
        }
    }

    /**
     * Calculate weighted average cost.
     */
    protected function calculateWeightedAverageCost(
        float $existingQty,
        float $existingCost,
        float $newQty,
        float $newCost
    ): float {
        $totalQty = $existingQty + $newQty;

        if ($totalQty <= 0) {
            return $newCost;
        }

        $existingValue = $existingQty * $existingCost;
        $newValue = $newQty * $newCost;

        return ($existingValue + $newValue) / $totalQty;
    }
}
