<?php

namespace App\Modules\Manufacturing\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

/**
 * WorkOrderOperation model - operations to complete in a work order.
 * Uses the 'manufacturing.work_order_operations' table in PostgreSQL.
 */
class WorkOrderOperation extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'manufacturing.work_order_operations';

    protected $fillable = [
        'organization_id',
        'work_order_id',
        'sequence',
        'operation_name',
        'work_center_id',
        'planned_time_minutes',
        'actual_time_minutes',
        'setup_time_minutes',
        'status',
        'started_at',
        'completed_at',
        'completed_by',
        'notes',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'planned_time_minutes' => 'decimal:2',
        'actual_time_minutes' => 'decimal:2',
        'setup_time_minutes' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the work order.
     */
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    /**
     * Get the work center.
     */
    public function workCenter()
    {
        return $this->belongsTo(WorkCenter::class, 'work_center_id');
    }

    /**
     * Get the user who completed this operation.
     */
    public function completedBy()
    {
        return $this->belongsTo(\App\Modules\Auth\Models\User::class, 'completed_by');
    }

    /**
     * Check if operation is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'COMPLETED';
    }

    /**
     * Get duration in minutes.
     */
    public function getDurationMinutesAttribute(): ?float
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }
        return $this->started_at->diffInMinutes($this->completed_at);
    }
}
