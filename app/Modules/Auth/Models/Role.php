<?php

namespace App\Modules\Auth\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

/**
 * Role model for RBAC.
 * Uses the 'auth.roles' table in PostgreSQL.
 */
class Role extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'auth.roles';

    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * Get the permissions assigned to this role.
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'auth.role_permissions',
            'role_id',
            'permission_id'
        )->withTimestamps();
    }

    /**
     * Get the users that have this role.
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'auth.user_roles',
            'role_id',
            'user_id'
        )->withTimestamps();
    }

    /**
     * Assign a permission to this role.
     */
    public function givePermission(Permission $permission): void
    {
        $this->permissions()->syncWithoutDetaching([$permission->id]);
    }

    /**
     * Remove a permission from this role.
     */
    public function revokePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }
}
