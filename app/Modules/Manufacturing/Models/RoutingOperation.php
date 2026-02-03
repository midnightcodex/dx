<?php

namespace App\Modules\Manufacturing\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

/**
 * RoutingOperation model - steps in a manufacturing routing.
 * Uses the 'manufacturing.routing_operations' table in PostgreSQL.
 */
class RoutingOperation extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'manufacturing.routing_operations';

    protected $fillable = [
        'organization_id',
        'routing_id',
        'sequence',
        'operation_name',
        'work_center_id',
        'setup_time_minutes',
        'run_time_per_unit',
        'labor_hours_per_unit',
        'quality_check_required',
        'instructions',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'setup_time_minutes' => 'integer',
        'run_time_per_unit' => 'decimal:2',
        'labor_hours_per_unit' => 'decimal:4',
        'quality_check_required' => 'boolean',
    ];

    /**
     * Get the routing.
     */
    public function routing()
    {
        return $this->belongsTo(Routing::class, 'routing_id');
    }

    /**
     * Get the work center.
     */
    public function workCenter()
    {
        return $this->belongsTo(WorkCenter::class, 'work_center_id');
    }

    /**
     * Calculate total time for a given quantity.
     */
    public function calculateTotalTime(float $quantity): float
    {
        return (float) $this->setup_time_minutes + ($quantity * (float) $this->run_time_per_unit);
    }
}
