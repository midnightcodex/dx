<?php

namespace App\Modules\Manufacturing\Models;

use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'manufacturing.cost_centers';

    protected $fillable = [
        'organization_id',
        'code',
        'name',
        'category',
        'is_active',
    ];
}
