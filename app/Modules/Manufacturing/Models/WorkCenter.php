<?php

namespace App\Modules\Manufacturing\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

/**
 * WorkCenter model - machines/workstations.
 * Uses the 'manufacturing.work_centers' table in PostgreSQL.
 */
class WorkCenter extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'manufacturing.work_centers';

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'description',
        'type',
        'hourly_rate',
        'capacity_per_hour',
        'warehouse_id',
        'is_active',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'capacity_per_hour' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    /**
     * Get the warehouse.
     */
    public function warehouse()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Warehouse::class, 'warehouse_id');
    }

    /**
     * Get work order operations at this center.
     */
    public function workOrderOperations()
    {
        return $this->hasMany(WorkOrderOperation::class, 'work_center_id');
    }

    /**
     * Scope for active work centers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
