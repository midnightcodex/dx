<?php

namespace App\Modules\Maintenance\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasUuid, BelongsToOrganization, SoftDeletes;

    protected $table = 'maintenance.equipment';

    protected $fillable = [
        'organization_id',
        'code',
        'name',
        'status', // OPERATIONAL, DOWN, MAINTENANCE
        'location',
        'serial_number',
        'model_number',
        'manufacturer',
        'purchase_date',
        'last_maintenance_date',
        'next_maintenance_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
    ];

    public function tickets()
    {
        return $this->hasMany(MaintenanceTicket::class, 'equipment_id');
    }
}
