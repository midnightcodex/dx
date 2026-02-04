<?php

namespace App\Modules\HR\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasUuid, BelongsToOrganization, SoftDeletes;

    protected $table = 'hr.employees';

    protected $fillable = [
        'organization_id',
        'user_id',
        'department_id',
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'designation',
        'date_of_joining',
        'status',
        'bank_name',
        'bank_account_number',
        'tax_id',
    ];

    protected $casts = [
        'date_of_joining' => 'date',
    ];

    // Accessor for Full Name
    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Modules\Auth\Models\User::class, 'user_id');
    }

    public function leaveAllocations()
    {
        return $this->hasMany(LeaveAllocation::class, 'employee_id');
    }

    public function salaryStructure()
    {
        return $this->hasMany(EmployeeSalaryStructure::class, 'employee_id');
    }
}
