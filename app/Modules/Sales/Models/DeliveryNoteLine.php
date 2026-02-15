<?php

namespace App\Modules\Sales\Models;

use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class DeliveryNoteLine extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'sales.delivery_note_lines';

    protected $fillable = [
        'organization_id',
        'delivery_note_id',
        'line_number',
        'sales_order_line_id',
        'item_id',
        'quantity',
        'uom_id',
        'batch_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'line_number' => 'integer',
    ];

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class, 'delivery_note_id');
    }

    public function salesOrderLine()
    {
        return $this->belongsTo(SalesOrderLine::class, 'sales_order_line_id');
    }
}
