<?php

namespace App\Modules\Inventory\Models;

use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasUuid, BelongsToOrganization;

    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_PENDING_APPROVAL = 'PENDING_APPROVAL';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_POSTED = 'POSTED';

    protected $table = 'inventory.stock_adjustments';

    protected $fillable = [
        'organization_id',
        'adjustment_number',
        'warehouse_id',
        'adjustment_type',
        'status',
        'reason',
        'created_by',
        'approved_by',
        'approved_at',
        'posted_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(StockAdjustmentLine::class, 'stock_adjustment_id');
    }
}
