<?php

namespace App\Modules\Shared\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'shared.item_categories';

    protected $fillable = [
        'organization_id',
        'code',
        'name',
        'description',
        'parent_id',
        'is_active',
    ];
}
