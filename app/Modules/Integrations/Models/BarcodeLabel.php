<?php

namespace App\Modules\Integrations\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class BarcodeLabel extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'integrations.barcode_labels';

    protected $fillable = [
        'organization_id',
        'label_type',
        'entity_type',
        'entity_id',
        'barcode',
        'barcode_format',
        'generated_at',
        'printed_at',
        'is_active',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'printed_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
