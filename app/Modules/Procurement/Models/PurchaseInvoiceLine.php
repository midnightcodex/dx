<?php

namespace App\Modules\Procurement\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceLine extends Model
{
    use HasUuid;

    protected $table = 'procurement.purchase_invoice_lines';

    protected $fillable = [
        'purchase_invoice_id',
        'line_number',
        'item_id',
        'quantity',
        'unit_price',
        'tax_amount',
        'line_amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'line_amount' => 'decimal:2',
    ];
}
