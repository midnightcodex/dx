<?php

namespace App\Modules\Shared\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class NumberSeries extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'shared.number_series';

    protected $fillable = [
        'organization_id',
        'entity_type',
        'prefix',
        'suffix',
        'format',
        'current_number',
        'padding',
        'include_date',
        'date_format',
        'reset_on_date_change',
        'last_reset_date',
    ];
}
