<?php

namespace App\Modules\Auth\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/**
 * Permission model for RBAC.
 * Uses the 'auth.permissions' table in PostgreSQL.
 */
class Permission extends Model
{
    use HasUuid;

    protected $table = 'auth.permissions';

    protected $fillable = [
        'name',
        'slug',
        'module',
        'description',
    ];

    /**
     * Get the roles that have this permission.
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'auth.role_permissions',
            'permission_id',
            'role_id'
        )->withTimestamps();
    }

    /**
     * Scope to filter by module.
     */
    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }
}
