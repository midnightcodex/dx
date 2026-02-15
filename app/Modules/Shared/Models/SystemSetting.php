<?php

namespace App\Modules\Shared\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'shared.system_settings';

    protected $fillable = [
        'organization_id',
        'setting_key',
        'setting_value',
        'setting_type',
        'module',
    ];
}
