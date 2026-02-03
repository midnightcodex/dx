<?php

namespace App\Modules\Inventory\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

/**
 * StockTransaction model - THE FACTS (immutable event log).
 * Uses the 'inventory.stock_transactions' table in PostgreSQL.
 * 
 * CRITICAL: This is an APPEND-ONLY table!
 * - Never UPDATE transactions
 * - Never DELETE transactions
 * - To cancel, use InventoryPostingService::cancelTransaction() which creates a reversal
 */
class StockTransaction extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'inventory.stock_transactions';

    // Explicitly disable updates at model level
    public static function boot()
    {
        parent::boot();

        // Prevent updates on this model (append-only)
        static::updating(function ($model) {
            // Only allow updating is_cancelled and cancelled_reason
            $dirty = $model->getDirty();
            $allowedFields = ['is_cancelled', 'cancelled_reason'];

            foreach (array_keys($dirty) as $field) {
                if (!in_array($field, $allowedFields)) {
                    throw new \RuntimeException(
                        "Stock transactions are immutable. Cannot update field: {$field}. " .
                        "Use InventoryPostingService::cancelTransaction() to create a reversal."
                    );
                }
            }
        });

        // Prevent deletes
        static::deleting(function ($model) {
            throw new \RuntimeException(
                "Stock transactions cannot be deleted. " .
                "Use InventoryPostingService::cancelTransaction() to create a reversal."
            );
        });
    }

    protected $fillable = [
        'organization_id',
        'transaction_type',
        'item_id',
        'warehouse_id',
        'batch_id',
        'quantity',
        'unit_cost',
        'total_value',
        'reference_type',
        'reference_id',
        'balance_after',
        'is_cancelled',
        'cancelled_reason',
        'created_by',
        'transaction_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_value' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'is_cancelled' => 'boolean',
        'transaction_date' => 'datetime',
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
     * Get the batch.
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    /**
     * Get the user who created this transaction.
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\Modules\Auth\Models\User::class, 'created_by');
    }

    /**
     * Scope for receipts (positive quantity).
     */
    public function scopeReceipts($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope for issues (negative quantity).
     */
    public function scopeIssues($query)
    {
        return $query->where('quantity', '<', 0);
    }

    /**
     * Scope to exclude cancelled transactions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_cancelled', false);
    }
}
