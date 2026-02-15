<?php

namespace App\Modules\Manufacturing\Models;

use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ScrapEntry extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'manufacturing.scrap_entries';

    protected $fillable = [
        'organization_id',
        'scrap_number',
        'source_type',
        'source_id',
        'item_id',
        'scrap_quantity',
        'scrap_value',
        'scrap_reason',
        'scrap_category',
        'warehouse_id',
        'batch_id',
        'disposal_method',
        'disposed_quantity',
        'disposal_date',
        'recorded_by',
        'inventory_transaction_id',
    ];

    protected $casts = [
        'scrap_quantity' => 'decimal:4',
        'scrap_value' => 'decimal:4',
        'disposed_quantity' => 'decimal:4',
        'disposal_date' => 'date',
    ];

    public function recovery()
    {
        return $this->hasMany(ScrapRecovery::class, 'scrap_entry_id');
    }
}
