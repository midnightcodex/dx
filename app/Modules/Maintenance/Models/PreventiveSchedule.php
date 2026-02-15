<?php

namespace App\Modules\Maintenance\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class PreventiveSchedule extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'maintenance.preventive_schedules';

    protected $fillable = [
        'organization_id',
        'schedule_code',
        'machine_id',
        'frequency_type',
        'frequency_value',
        'checklist_template_id',
        'last_performed_date',
        'next_due_date',
        'assigned_to',
        'is_active',
    ];

    protected $casts = [
        'last_performed_date' => 'date',
        'next_due_date' => 'date',
        'is_active' => 'boolean',
    ];
}
