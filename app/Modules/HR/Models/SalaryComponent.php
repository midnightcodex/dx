<?php

namespace App\Modules\HR\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class SalaryComponent extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'hr.salary_components';

    protected $fillable = [
        'organization_id',
        'name',
        'type', // EARNING, DEDUCTION
        'is_fixed',
    ];
}
