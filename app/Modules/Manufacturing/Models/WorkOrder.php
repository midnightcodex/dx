<?php

namespace App\Modules\Manufacturing\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * WorkOrder model - production execution document.
 * Uses the 'manufacturing.work_orders' table in PostgreSQL.
 */
class WorkOrder extends Model
{
    use HasUuid, BelongsToOrganization, HasAuditFields, SoftDeletes;

    protected $table = 'manufacturing.work_orders';

    // Status constants
    const STATUS_PLANNED = 'PLANNED';
    const STATUS_RELEASED = 'RELEASED';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_CANCELLED = 'CANCELLED';

    protected $fillable = [
        'organization_id',
        'wo_number',
        'item_id',
        'bom_id',
        'routing_id',
        'planned_quantity',
        'completed_quantity',
        'rejected_quantity',
        'source_warehouse_id',
        'target_warehouse_id',
        'status',
        'scheduled_start_date',
        'scheduled_end_date',
        'actual_start_at',
        'actual_end_at',
        'priority',
        'production_plan_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:4',
        'completed_quantity' => 'decimal:4',
        'rejected_quantity' => 'decimal:4',
        'scheduled_start_date' => 'date',
        'scheduled_end_date' => 'date',
        'actual_start_at' => 'datetime',
        'actual_end_at' => 'datetime',
        'priority' => 'integer',
    ];

    /**
     * Get the finished good item.
     */
    public function item()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Item::class, 'item_id');
    }

    /**
     * Get the BOM used.
     */
    public function bom()
    {
        return $this->belongsTo(BomHeader::class, 'bom_id');
    }

    /**
     * Get the source warehouse (raw materials).
     */
    public function sourceWarehouse()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Warehouse::class, 'source_warehouse_id');
    }

    /**
     * Get the target warehouse (finished goods).
     */
    public function targetWarehouse()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Warehouse::class, 'target_warehouse_id');
    }

    /**
     * Get the materials required for this work order.
     */
    public function materials()
    {
        return $this->hasMany(WorkOrderMaterial::class, 'work_order_id');
    }

    /**
     * Get the operations for this work order.
     */
    public function operations()
    {
        return $this->hasMany(WorkOrderOperation::class, 'work_order_id');
    }

    /**
     * Get production logs.
     */
    public function productionLogs()
    {
        return $this->hasMany(ProductionLog::class, 'work_order_id');
    }

    /**
     * Get the user who created this work order.
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\Modules\Auth\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated this work order.
     */
    public function updatedBy()
    {
        return $this->belongsTo(\App\Modules\Auth\Models\User::class, 'updated_by');
    }

    /**
     * Calculate completion percentage.
     */
    public function getCompletionPercentageAttribute(): float
    {
        if ((float) $this->planned_quantity <= 0) {
            return 0;
        }

        return min(100, ((float) $this->completed_quantity / (float) $this->planned_quantity) * 100);
    }

    /**
     * Check if work order can be released.
     */
    public function canBeReleased(): bool
    {
        return $this->status === self::STATUS_PLANNED;
    }

    /**
     * Check if work order can start production.
     */
    public function canStartProduction(): bool
    {
        return $this->status === self::STATUS_RELEASED;
    }

    /**
     * Check if work order can be completed.
     */
    public function canBeCompleted(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Scope for work orders by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for today's work.
     */
    public function scopeScheduledForToday($query)
    {
        return $query->whereDate('scheduled_start_date', today());
    }
}
