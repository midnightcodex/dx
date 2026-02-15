<?php

namespace App\Modules\Maintenance\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class PreventiveTask extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'maintenance.preventive_tasks';

    protected $fillable = [
        'organization_id',
        'task_number',
        'schedule_id',
        'machine_id',
        'scheduled_date',
        'completed_date',
        'status',
        'assigned_to',
        'performed_by',
        'duration_minutes',
        'findings',
        'actions_taken',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
    ];
}
