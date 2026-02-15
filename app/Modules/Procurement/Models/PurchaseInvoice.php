<?php

namespace App\Modules\Procurement\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'procurement.purchase_invoices';

    protected $fillable = [
        'organization_id',
        'invoice_number',
        'vendor_id',
        'purchase_order_id',
        'grn_id',
        'invoice_date',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'currency',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];
}
