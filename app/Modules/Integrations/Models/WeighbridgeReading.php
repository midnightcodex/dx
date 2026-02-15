<?php

namespace App\Modules\Integrations\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class WeighbridgeReading extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'integrations.weighbridge_readings';

    protected $fillable = [
        'organization_id',
        'reading_number',
        'reading_date',
        'vehicle_number',
        'tare_weight',
        'gross_weight',
        'net_weight',
        'uom',
        'reference_type',
        'reference_id',
        'weighbridge_operator',
    ];

    protected $casts = [
        'reading_date' => 'datetime',
        'tare_weight' => 'decimal:3',
        'gross_weight' => 'decimal:3',
        'net_weight' => 'decimal:3',
    ];
}
