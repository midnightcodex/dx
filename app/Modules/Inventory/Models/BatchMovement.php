<?php

namespace App\Modules\Inventory\Models;

use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class BatchMovement extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'inventory.batch_movements';

    protected $fillable = [
        'organization_id',
        'batch_id',
        'stock_transaction_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'quantity',
        'movement_type',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];
}
