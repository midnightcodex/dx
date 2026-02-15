<?php

namespace App\Modules\Manufacturing\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class ProductionPlanItem extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'manufacturing.production_plan_items';

    protected $fillable = [
        'organization_id',
        'production_plan_id',
        'item_id',
        'planned_quantity',
        'scheduled_start_date',
        'scheduled_end_date',
        'priority',
        'demand_source',
        'demand_reference_id',
        'work_orders_generated',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:4',
        'scheduled_start_date' => 'date',
        'scheduled_end_date' => 'date',
        'work_orders_generated' => 'boolean',
    ];
}
