<?php

namespace App\Modules\Sales\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'sales.sales_returns';

    protected $fillable = [
        'organization_id',
        'return_number',
        'customer_id',
        'sales_order_id',
        'delivery_note_id',
        'return_date',
        'status',
        'reason',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'return_date' => 'date',
        'approved_at' => 'datetime',
    ];
}
