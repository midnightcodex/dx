<?php

namespace App\Modules\Manufacturing\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

/**
 * Routing model - manufacturing process templates.
 * Uses the 'manufacturing.routings' table in PostgreSQL.
 */
class Routing extends Model
{
    use HasUuid, BelongsToOrganization, HasAuditFields;

    protected $table = 'manufacturing.routings';

    protected $fillable = [
        'organization_id',
        'routing_number',
        'name',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the operations for this routing.
     */
    public function operations()
    {
        return $this->hasMany(RoutingOperation::class, 'routing_id')->orderBy('sequence');
    }

    /**
     * Get BOMs that use this routing.
     */
    public function boms()
    {
        return $this->hasMany(BomHeader::class, 'routing_id');
    }

    /**
     * Scope for active routings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
