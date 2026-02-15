<?php

namespace App\Modules\Manufacturing\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ScrapRecovery extends Model
{
    use HasUuid;

    protected $table = 'manufacturing.scrap_recovery';

    protected $fillable = [
        'scrap_entry_id',
        'recovered_item_id',
        'recovered_quantity',
        'recovery_value',
        'recovery_date',
        'sold_to',
        'notes',
    ];

    protected $casts = [
        'recovered_quantity' => 'decimal:4',
        'recovery_value' => 'decimal:4',
        'recovery_date' => 'date',
    ];
}
