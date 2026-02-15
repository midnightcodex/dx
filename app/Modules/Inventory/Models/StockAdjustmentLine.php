<?php

namespace App\Modules\Inventory\Models;

use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class StockAdjustmentLine extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'inventory.stock_adjustment_lines';

    protected $fillable = [
        'organization_id',
        'stock_adjustment_id',
        'item_id',
        'batch_id',
        'system_quantity',
        'actual_quantity',
        'difference',
        'unit_cost',
        'notes',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:4',
        'actual_quantity' => 'decimal:4',
        'difference' => 'decimal:4',
        'unit_cost' => 'decimal:4',
    ];

    public function adjustment()
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }
}
