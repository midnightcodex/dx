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
        'line_number',
        'item_id',
        'quantity',
        'uom_id',
        'unit_price',
        'tax_amount',
        'line_amount',
        'reserved_quantity',
        'dispatched_quantity',
        'reserved_warehouse_id',
        'reserved_batch_id',
    ];

    protected $casts = [
        'line_number' => 'integer',
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_amount' => 'decimal:2',
        'reserved_quantity' => 'decimal:4',
        'dispatched_quantity' => 'decimal:4',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }
}
