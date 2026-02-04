<?php

namespace App\Modules\Sales\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasUuid, BelongsToOrganization, HasAuditFields, SoftDeletes;

    protected $table = 'sales.sales_orders';

    protected $fillable = [
        'organization_id',
        'so_number',
        'customer_id',
        'order_date',
        'expected_ship_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'notes',
        'shipping_address_snapshot',
        'billing_address_snapshot',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_ship_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function lines()
    {
        return $this->hasMany(SalesOrderLine::class, 'sales_order_id');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'sales_order_id');
    }
}
