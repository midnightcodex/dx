<?php

namespace App\Modules\Inventory\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Item model - products, materials, components.
 * Uses the 'inventory.items' table in PostgreSQL.
 */
class Item extends Model
{
    use HasUuid, BelongsToOrganization, HasAuditFields, SoftDeletes;

    protected $table = 'inventory.items';

    protected $fillable = [
        'organization_id',
        'item_code',
        'name',
        'description',
        'category_id',
        'primary_uom_id',
        'item_type',
        'stock_type',
        'is_batch_tracked',
        'is_serial_tracked',
        'reorder_level',
        'reorder_quantity',
        'safety_stock',
        'lead_time_days',
        'standard_cost',
        'hs_code',
        'barcode',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_batch_tracked' => 'boolean',
        'is_serial_tracked' => 'boolean',
        'is_active' => 'boolean',
        'reorder_level' => 'decimal:4',
        'reorder_quantity' => 'decimal:4',
        'safety_stock' => 'decimal:4',
        'lead_time_days' => 'decimal:1',
        'standard_cost' => 'decimal:4',
    ];

    /**
     * Get the category for this item.
     */
    public function category()
    {
        return $this->belongsTo(\App\Modules\Shared\Models\ItemCategory::class, 'category_id');
    }

    /**
     * Get the primary UOM for this item.
     */
    public function primaryUom()
    {
        return $this->belongsTo(\App\Modules\Shared\Models\Uom::class, 'primary_uom_id');
    }

    /**
     * Get stock ledger entries for this item.
     */
    public function stockLedgers()
    {
        return $this->hasMany(StockLedger::class, 'item_id');
    }

    /**
     * Get stock transactions for this item.
     */
    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class, 'item_id');
    }

    /**
     * Get batches for this item.
     */
    public function batches()
    {
        return $this->hasMany(Batch::class, 'item_id');
    }

    /**
     * Get total available stock across all warehouses.
     */
    public function getTotalStockAttribute(): float
    {
        return $this->stockLedgers()->sum('quantity_available');
    }

    /**
     * Get available stock in a specific warehouse.
     */
    public function getStockInWarehouse(string $warehouseId): float
    {
        $ledger = $this->stockLedgers()
            ->where('warehouse_id', $warehouseId)
            ->first();

        return $ledger ? (float) $ledger->quantity_available : 0;
    }
}
