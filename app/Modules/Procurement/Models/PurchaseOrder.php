<?php

namespace App\Modules\Procurement\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * PurchaseOrder model.
 * Uses the 'procurement.purchase_orders' table in PostgreSQL.
 */
class PurchaseOrder extends Model
{
    use HasUuid, BelongsToOrganization, HasAuditFields, SoftDeletes;

    protected $table = 'procurement.purchase_orders';

    // Status constants
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_SUBMITTED = 'SUBMITTED';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_PARTIAL = 'PARTIAL';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_CANCELLED = 'CANCELLED';

    protected $fillable = [
        'organization_id',
        'po_number',
        'vendor_id',
        'order_date',
        'expected_date',
        'delivery_warehouse_id',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'exchange_rate',
        'payment_terms',
        'delivery_address',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the vendor.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    /**
     * Get the delivery warehouse.
     */
    public function deliveryWarehouse()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Warehouse::class, 'delivery_warehouse_id');
    }

    /**
     * Get the PO lines.
     */
    public function lines()
    {
        return $this->hasMany(PurchaseOrderLine::class, 'purchase_order_id');
    }

    /**
     * Get GRNs for this PO.
     */
    public function goodsReceiptNotes()
    {
        return $this->hasMany(GoodsReceiptNote::class, 'purchase_order_id');
    }

    /**
     * Calculate total from lines.
     */
    public function calculateTotals(): void
    {
        $this->subtotal = $this->lines->sum('line_amount');
        $this->tax_amount = $this->lines->sum('tax_amount');
        $this->total_amount = $this->subtotal + $this->tax_amount - (float) $this->discount_amount;
    }

    /**
     * Check if PO is fully received.
     */
    public function isFullyReceived(): bool
    {
        foreach ($this->lines as $line) {
            if ((float) $line->received_quantity < (float) $line->quantity) {
                return false;
            }
        }
        return true;
    }
}
