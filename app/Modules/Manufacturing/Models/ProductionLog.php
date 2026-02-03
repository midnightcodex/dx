<?php

namespace App\Modules\Manufacturing\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

/**
 * ProductionLog model - actual production recorded.
 * Uses the 'manufacturing.production_logs' table in PostgreSQL.
 */
class ProductionLog extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'manufacturing.production_logs';

    protected $fillable = [
        'organization_id',
        'work_order_id',
        'work_order_operation_id',
        'work_center_id',
        'quantity_produced',
        'quantity_rejected',
        'rejection_reason',
        'production_date',
        'shift_id',
        'operator_id',
        'notes',
    ];

    protected $casts = [
        'quantity_produced' => 'decimal:4',
        'quantity_rejected' => 'decimal:4',
        'production_date' => 'datetime',
    ];

    /**
     * Get the work order.
     */
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    /**
     * Get the work order operation.
     */
    public function workOrderOperation()
    {
        return $this->belongsTo(WorkOrderOperation::class, 'work_order_operation_id');
    }

    /**
     * Get the work center.
     */
    public function workCenter()
    {
        return $this->belongsTo(WorkCenter::class, 'work_center_id');
    }

    /**
     * Get the operator.
     */
    public function operator()
    {
        return $this->belongsTo(\App\Modules\Auth\Models\User::class, 'operator_id');
    }

    /**
     * Get good quantity (produced - rejected).
     */
    public function getGoodQuantityAttribute(): float
    {
        return (float) $this->quantity_produced - (float) $this->quantity_rejected;
    }

    /**
     * Get rejection rate as percentage.
     */
    public function getRejectionRateAttribute(): float
    {
        if ((float) $this->quantity_produced <= 0) {
            return 0;
        }
        return ((float) $this->quantity_rejected / (float) $this->quantity_produced) * 100;
    }
}
