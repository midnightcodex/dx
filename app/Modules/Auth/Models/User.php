<?php

namespace App\Modules\Auth\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User model for authentication.
 * Uses the 'auth.users' table in PostgreSQL.
 * 
 * @property string $id
 * @property string $organization_id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property bool $is_active
 */
class User extends Authenticatable
{
    use HasUuid, BelongsToOrganization, Notifiable, SoftDeletes;

    protected $table = 'auth.users';

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'password',
        'phone',
        'avatar_url',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Get the organization this user belongs to.
     */
    public function organization()
    {
        return $this->belongsTo(\App\Modules\Shared\Models\Organization::class, 'organization_id');
    }

    /**
     * Get the roles assigned to this user.
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'auth.user_roles',
            'user_id',
            'role_id'
        )->withTimestamps();
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionSlug) {
                $query->where('slug', $permissionSlug);
            })
            ->exists();
    }

    /**
     * Get all permissions for this user through their roles.
     */
    public function permissions()
    {
        return Permission::whereIn('id', function ($query) {
            $query->select('permission_id')
                ->from('auth.role_permissions')
                ->whereIn('role_id', $this->roles()->pluck('id'));
        });
    }
}
