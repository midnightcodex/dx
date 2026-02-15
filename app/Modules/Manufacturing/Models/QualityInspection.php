<?php

namespace App\Modules\Manufacturing\Models;

use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class QualityInspection extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'manufacturing.quality_inspections';

    protected $fillable = [
        'organization_id',
        'inspection_number',
        'template_id',
        'reference_type',
        'reference_id',
        'item_id',
        'batch_id',
        'quantity_inspected',
        'inspection_date',
        'inspected_by',
        'status',
        'overall_result',
        'remarks',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'quantity_inspected' => 'decimal:4',
        'inspection_date' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function readings()
    {
        return $this->hasMany(QualityInspectionReading::class, 'inspection_id');
    }
}
