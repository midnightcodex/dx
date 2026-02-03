<?php

namespace App\Modules\Shared\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class Uom extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'shared.uom';

    protected $fillable = [
        'organization_id',
        'symbol',
        'name',
        'category', // COUNT, WEIGHT, LENGTH, TIME, VOLUME
        'is_active',
        'conversion_factor',
        'base_uom_id',
    ];
}
