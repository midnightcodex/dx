<?php

namespace App\Modules\Maintenance\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasUuid, BelongsToOrganization, HasAuditFields;

    protected $table = 'maintenance.machines';

    protected $fillable = [
        'organization_id',
        'machine_code',
        'machine_name',
        'machine_type',
        'manufacturer',
        'model_number',
        'serial_number',
        'purchase_date',
        'installation_date',
        'warranty_expiry_date',
        'location',
        'work_center_id',
        'capacity',
        'power_rating',
        'maintenance_frequency_days',
        'last_maintenance_date',
        'next_maintenance_date',
        'status',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'purchase_date' => 'date',
        'installation_date' => 'date',
        'warranty_expiry_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
    ];
}
