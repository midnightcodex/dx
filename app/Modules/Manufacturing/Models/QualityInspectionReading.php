<?php

namespace App\Modules\Manufacturing\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class QualityInspectionReading extends Model
{
    use HasUuid;

    protected $table = 'manufacturing.quality_inspection_readings';

    protected $fillable = [
        'inspection_id',
        'parameter_id',
        'reading_value',
        'numeric_value',
        'is_within_spec',
        'notes',
    ];

    protected $casts = [
        'numeric_value' => 'decimal:4',
        'is_within_spec' => 'boolean',
    ];
}
