<?php

namespace App\Modules\HR\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasUuid, BelongsToOrganization, HasAuditFields;

    protected $table = 'hr.shifts';

    protected $fillable = [
        'organization_id',
        'shift_code',
        'shift_name',
        'start_time',
        'end_time',
        'break_duration_minutes',
        'is_night_shift',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_night_shift' => 'boolean',
    ];
}
