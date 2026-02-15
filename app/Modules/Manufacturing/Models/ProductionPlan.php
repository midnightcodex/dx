<?php

namespace App\Modules\Manufacturing\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class ProductionPlan extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'manufacturing.production_plans';

    protected $fillable = [
        'organization_id',
        'plan_number',
        'plan_date',
        'planning_period_start',
        'planning_period_end',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'plan_date' => 'date',
        'planning_period_start' => 'date',
        'planning_period_end' => 'date',
        'approved_at' => 'datetime',
    ];
}
