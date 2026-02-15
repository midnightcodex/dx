<?php

namespace App\Modules\Maintenance\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class BreakdownReport extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'maintenance.breakdown_reports';

    protected $fillable = [
        'organization_id',
        'ticket_number',
        'machine_id',
        'reported_at',
        'reported_by',
        'problem_description',
        'severity',
        'status',
        'assigned_to',
        'work_started_at',
        'work_completed_at',
        'downtime_minutes',
        'production_loss_estimate',
        'root_cause',
        'corrective_action',
        'preventive_action',
        'spare_parts_used',
        'labor_cost',
        'parts_cost',
        'total_cost',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'work_started_at' => 'datetime',
        'work_completed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];
}
