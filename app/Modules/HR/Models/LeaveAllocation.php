<?php

namespace App\Modules\HR\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class LeaveAllocation extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'hr.leave_allocations';

    protected $fillable = [
        'organization_id',
        'employee_id',
        'leave_type_id',
        'days_allocated',
        'days_used',
        'year',
    ];

    public function type()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}
