<?php

namespace App\Modules\Maintenance\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceTicket extends Model
{
    use HasUuid, BelongsToOrganization, SoftDeletes;

    protected $table = 'maintenance.maintenance_tickets';

    protected $fillable = [
        'organization_id',
        'ticket_number',
        'equipment_id',
        'reported_by',
        'assigned_to',
        'subject',
        'description',
        'priority',
        'status',
        'completed_at',
        'resolution_notes',
        'created_by',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function reporter()
    {
        return $this->belongsTo(\App\Modules\Auth\Models\User::class, 'reported_by');
    }

    public function assignee()
    {
        return $this->belongsTo(\App\Modules\Auth\Models\User::class, 'assigned_to');
    }
}
