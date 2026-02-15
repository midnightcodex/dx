<?php

namespace App\Modules\Integrations\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class ApiConfiguration extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'integrations.api_configurations';

    protected $fillable = [
        'organization_id',
        'integration_name',
        'api_endpoint',
        'auth_type',
        'credentials',
        'is_active',
        'last_sync_at',
        'sync_frequency_minutes',
    ];

    protected $casts = [
        'credentials' => 'array',
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
    ];
}
