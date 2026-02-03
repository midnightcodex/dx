<?php

namespace App\Modules\Procurement\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

/**
 * PurchaseOrderLine model.
 * Uses the 'procurement.purchase_order_lines' table in PostgreSQL.
 */
class PurchaseOrderLine extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'procurement.purchase_order_lines';

    protected $fillable = [
        'organization_id',
        'purchase_order_id',
        'line_number',
        'item_id',
        'description',
        'quantity',
        'uom_id',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'discount_percentage',
        'discount_amount',
        'line_amount',
        'received_quantity',
        'expected_date',
    ];

    protected $casts = [
        'line_number' => 'integer',
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'line_amount' => 'decimal:2',
        'received_quantity' => 'decimal:4',
        'expected_date' => 'date',
    ];

    /**
     * Get the purchase order.
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    /**
     * Get the item.
     */
    public function item()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Item::class, 'item_id');
    }

    /**
     * Get pending quantity to receive.
     */
    public function getPendingQuantityAttribute(): float
    {
        return max(0, (float) $this->quantity - (float) $this->received_quantity);
    }

    /**
     * Calculate line amount.
     */
    public function calculateLineAmount(): void
    {
        $baseAmount = (float) $this->quantity * (float) $this->unit_price;
        $discountAmount = $baseAmount * ((float) $this->discount_percentage / 100);
        $netAmount = $baseAmount - $discountAmount;
        $taxAmount = $netAmount * ((float) $this->tax_rate / 100);

        $this->discount_amount = $discountAmount;
        $this->tax_amount = $taxAmount;
        $this->line_amount = $netAmount + $taxAmount;
    }
}
