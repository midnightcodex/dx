<?php

namespace App\Modules\HR\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends Model
{
    use HasUuid, BelongsToOrganization, SoftDeletes;

    protected $table = 'hr.leave_types';

    protected $fillable = [
        'organization_id',
        'name',
        'default_days_per_year',
    ];
}
