<?php

namespace App\Modules\Manufacturing\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

/**
 * WorkOrderMaterial model - materials required for a work order.
 * Uses the 'manufacturing.work_order_materials' table in PostgreSQL.
 */
class WorkOrderMaterial extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'manufacturing.work_order_materials';

    protected $fillable = [
        'organization_id',
        'work_order_id',
        'item_id',
        'batch_id',
        'required_quantity',
        'issued_quantity',
        'consumed_quantity',
        'returned_quantity',
        'warehouse_id',
        'operation_sequence',
        'status',
    ];

    protected $casts = [
        'required_quantity' => 'decimal:4',
        'issued_quantity' => 'decimal:4',
        'consumed_quantity' => 'decimal:4',
        'returned_quantity' => 'decimal:4',
        'operation_sequence' => 'integer',
    ];

    /**
     * Get the work order.
     */
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    /**
     * Get the item.
     */
    public function item()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Item::class, 'item_id');
    }

    /**
     * Get the warehouse.
     */
    public function warehouse()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the batch.
     */
    public function batch()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Batch::class, 'batch_id');
    }

    /**
     * Get pending quantity to issue.
     */
    public function getPendingQuantityAttribute(): float
    {
        return max(0, (float) $this->required_quantity - (float) $this->issued_quantity);
    }
}
