<?php

namespace App\Modules\Sales\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryNote extends Model
{
    use HasUuid, BelongsToOrganization, HasAuditFields, SoftDeletes;

    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_DISPATCHED = 'DISPATCHED';
    public const STATUS_DELIVERED = 'DELIVERED';
    public const STATUS_CANCELLED = 'CANCELLED';

    protected $table = 'sales.delivery_notes';

    protected $fillable = [
        'organization_id',
        'dn_number',
        'sales_order_id',
        'warehouse_id',
        'delivery_date',
        'status',
        'notes',
        'dispatched_at',
        'dispatched_by',
        'delivered_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'dispatched_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function lines()
    {
        return $this->hasMany(DeliveryNoteLine::class, 'delivery_note_id');
    }
}
