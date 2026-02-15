<?php

namespace App\Modules\Compliance\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'compliance.audit_logs';

    protected $fillable = [
        'organization_id',
        'entity_type',
        'entity_id',
        'action',
        'old_values',
        'new_values',
        'changed_by',
        'changed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_at' => 'datetime',
    ];
}
