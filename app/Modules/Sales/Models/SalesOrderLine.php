<?php

namespace App\Modules\Sales\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class SalesOrderLine extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'sales.sales_order_lines';

    protected $fillable = [
        'organization_id',
        'sales_order_id',
        'item_id',
        'quantity',
        'unit_price',
        'tax_rate',
        'line_amount',
        'shipped_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'tax_rate' => 'decimal:2',
        'line_amount' => 'decimal:2',
        'shipped_quantity' => 'decimal:4',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function item()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Item::class, 'item_id');
    }
}
