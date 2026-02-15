<?php

namespace App\Modules\HR\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class EmployeeShiftAssignment extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'hr.employee_shift_assignments';

    protected $fillable = [
        'organization_id',
        'employee_id',
        'shift_id',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];
}
