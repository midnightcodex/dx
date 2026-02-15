<?php

namespace App\Modules\Maintenance\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRequest extends Model
{
    use HasUuid, BelongsToOrganization, HasAuditFields, SoftDeletes;

    protected $table = 'maintenance.maintenance_requests';

    protected $fillable = [
        'organization_id',
        'machine_id',
        'request_type',
        'status',
        'priority',
        'description',
        'reported_by',
        'assigned_to',
        'scheduled_at',
        'completed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
