<?php

namespace App\Modules\HR\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasUuid, BelongsToOrganization, HasAuditFields;

    protected $table = 'hr.employees';

    protected $fillable = [
        'organization_id',
        'employee_code',
        'first_name',
        'last_name',
        'full_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'date_of_joining',
        'date_of_leaving',
        'department',
        'designation',
        'employment_type',
        'reporting_to',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_of_joining' => 'date',
        'date_of_birth' => 'date',
        'date_of_leaving' => 'date',
        'is_active' => 'boolean',
    ];
}
