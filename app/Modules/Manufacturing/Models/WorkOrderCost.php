<?php

namespace App\Modules\Manufacturing\Models;

use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class WorkOrderCost extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'manufacturing.work_order_costs';

    protected $fillable = [
        'organization_id',
        'work_order_id',
        'cost_type',
        'cost_center_id',
        'standard_cost',
        'actual_cost',
        'variance',
        'quantity',
        'rate',
        'calculation_date',
        'notes',
    ];

    protected $casts = [
        'standard_cost' => 'decimal:4',
        'actual_cost' => 'decimal:4',
        'variance' => 'decimal:4',
        'quantity' => 'decimal:4',
        'rate' => 'decimal:4',
        'calculation_date' => 'datetime',
    ];
}
