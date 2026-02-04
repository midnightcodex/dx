<?php

namespace App\Modules\Sales\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class ShipmentLine extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'sales.shipment_lines';

    protected $fillable = [
        'organization_id',
        'shipment_id',
        'sales_order_line_id',
        'item_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function salesOrderLine()
    {
        return $this->belongsTo(SalesOrderLine::class, 'sales_order_line_id');
    }

    public function item()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Item::class, 'item_id');
    }
}
