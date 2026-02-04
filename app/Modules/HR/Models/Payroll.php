<?php

namespace App\Modules\HR\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'hr.payrolls';

    protected $fillable = [
        'organization_id',
        'month',
        'year',
        'status',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];
}
