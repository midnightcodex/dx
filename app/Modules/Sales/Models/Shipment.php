<?php

namespace App\Modules\Sales\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'sales.shipments';

    protected $fillable = [
        'organization_id',
        'shipment_number',
        'sales_order_id',
        'warehouse_id',
        'shipment_date',
        'status',
        'carrier',
        'tracking_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'shipment_date' => 'date',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Warehouse::class, 'warehouse_id');
    }

    public function lines()
    {
        return $this->hasMany(ShipmentLine::class, 'shipment_id');
    }
}
