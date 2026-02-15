<?php

namespace App\Modules\Maintenance\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class MaintenanceChecklistItem extends Model
{
    use HasUuid;

    protected $table = 'maintenance.maintenance_checklist_items';

    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'item_number',
        'check_description',
        'check_type',
        'expected_value',
        'actual_value',
        'is_ok',
        'remarks',
    ];

    protected $casts = [
        'is_ok' => 'boolean',
    ];
}
