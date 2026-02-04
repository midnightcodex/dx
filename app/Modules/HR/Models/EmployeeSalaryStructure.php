<?php

namespace App\Modules\HR\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryStructure extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'hr.employee_salary_structures';

    protected $fillable = [
        'organization_id',
        'employee_id',
        'salary_component_id',
        'amount',
    ];

    public function component()
    {
        return $this->belongsTo(SalaryComponent::class, 'salary_component_id');
    }
}
