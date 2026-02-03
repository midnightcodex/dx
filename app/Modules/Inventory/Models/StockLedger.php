<?php

namespace App\Modules\Inventory\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

/**
 * StockLedger model - THE TRUTH (current stock state).
 * Uses the 'inventory.stock_ledger' table in PostgreSQL.
 * 
 * CRITICAL: This table should ONLY be written to by InventoryPostingService!
 * Never update this directly - always go through the posting service.
 */
class StockLedger extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'inventory.stock_ledger';

    protected $fillable = [
        'organization_id',
        'item_id',
        'warehouse_id',
        'batch_id',
        'quantity_available',
        'quantity_reserved',
        'quantity_in_transit',
        'unit_cost',
        'last_transaction_id',
        'last_updated',
    ];

    protected $casts = [
        'quantity_available' => 'decimal:4',
        'quantity_reserved' => 'decimal:4',
        'quantity_in_transit' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'last_updated' => 'datetime',
    ];

    /**
     * Get the item.
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * Get the warehouse.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the batch (if batch-tracked).
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    /**
     * Get the last transaction.
     */
    public function lastTransaction()
    {
        return $this->belongsTo(StockTransaction::class, 'last_transaction_id');
    }

    /**
     * Get net available quantity (available - reserved).
     */
    public function getNetAvailableAttribute(): float
    {
        return (float) $this->quantity_available - (float) $this->quantity_reserved;
    }

    /**
     * Scope to find by item and warehouse.
     */
    public function scopeForItemWarehouse($query, string $itemId, string $warehouseId, ?string $batchId = null)
    {
        return $query->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->where('batch_id', $batchId);
    }
}
