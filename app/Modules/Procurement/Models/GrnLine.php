<?php

namespace App\Modules\Procurement\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

/**
 * GrnLine model - GRN line items.
 * Uses the 'procurement.grn_lines' table in PostgreSQL.
 */
class GrnLine extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'procurement.grn_lines';

    protected $fillable = [
        'organization_id',
        'grn_id',
        'po_line_id',
        'item_id',
        'ordered_quantity',
        'received_quantity',
        'accepted_quantity',
        'rejected_quantity',
        'rejection_reason',
        'batch_id',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'unit_price',
        'quality_status',
    ];

    protected $casts = [
        'ordered_quantity' => 'decimal:4',
        'received_quantity' => 'decimal:4',
        'accepted_quantity' => 'decimal:4',
        'rejected_quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
    ];

    /**
     * Get the GRN.
     */
    public function grn()
    {
        return $this->belongsTo(GoodsReceiptNote::class, 'grn_id');
    }

    /**
     * Get the item.
     */
    public function item()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Item::class, 'item_id');
    }

    /**
     * Get the PO line.
     */
    public function poLine()
    {
        return $this->belongsTo(PurchaseOrderLine::class, 'po_line_id');
    }

    /**
     * Get the batch.
     */
    public function batch()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Batch::class, 'batch_id');
    }
}
