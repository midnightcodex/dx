<?php

namespace App\Modules\Inventory\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

/**
 * Batch model - for batch-tracked inventory items.
 * Uses the 'inventory.batches' table in PostgreSQL.
 */
class Batch extends Model
{
    use HasUuid, BelongsToOrganization, HasAuditFields;

    protected $table = 'inventory.batches';

    protected $fillable = [
        'organization_id',
        'item_id',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'supplier_batch',
        'vendor_id',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
    ];

    /**
     * Get the item this batch belongs to.
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * Get the vendor.
     */
    public function vendor()
    {
        return $this->belongsTo(\App\Modules\Procurement\Models\Vendor::class, 'vendor_id');
    }

    /**
     * Get stock ledger entries for this batch.
     */
    public function stockLedgers()
    {
        return $this->hasMany(StockLedger::class, 'batch_id');
    }

    /**
     * Check if batch is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    /**
     * Scope for active batches.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    /**
     * Scope for batches expiring soon.
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }
}
