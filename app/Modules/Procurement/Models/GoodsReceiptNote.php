<?php

namespace App\Modules\Procurement\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

/**
 * GoodsReceiptNote model - incoming goods from PO.
 * Uses the 'procurement.goods_receipt_notes' table in PostgreSQL.
 */
class GoodsReceiptNote extends Model
{
    use HasUuid, BelongsToOrganization, HasAuditFields;

    protected $table = 'procurement.goods_receipt_notes';

    const STATUS_DRAFT = 'DRAFT';
    const STATUS_INSPECTING = 'INSPECTING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_POSTED = 'POSTED';
    const STATUS_CANCELLED = 'CANCELLED';

    protected $fillable = [
        'organization_id',
        'grn_number',
        'purchase_order_id',
        'vendor_id',
        'warehouse_id',
        'receipt_date',
        'status',
        'supplier_invoice_number',
        'supplier_invoice_date',
        'notes',
        'received_by',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'supplier_invoice_date' => 'date',
        'posted_at' => 'datetime',
    ];

    /**
     * Get the purchase order.
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    /**
     * Get the vendor.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    /**
     * Get the warehouse.
     */
    public function warehouse()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the GRN lines.
     */
    public function lines()
    {
        return $this->hasMany(GrnLine::class, 'grn_id');
    }

    /**
     * Get the user who received the goods.
     */
    public function receivedBy()
    {
        return $this->belongsTo(\App\Modules\Auth\Models\User::class, 'received_by');
    }

    /**
     * Check if GRN can be posted.
     */
    public function canBePosted(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_DRAFT]);
    }
}
