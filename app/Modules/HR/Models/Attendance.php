<?php

namespace App\Modules\HR\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'hr.attendance';

    protected $fillable = [
        'organization_id',
        'employee_id',
        'shift_id',
        'attendance_date',
        'status',
        'clock_in_time',
        'clock_out_time',
        'work_duration_minutes',
        'late_arrival_minutes',
        'early_departure_minutes',
        'overtime_minutes',
        'remarks',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
    ];
}
