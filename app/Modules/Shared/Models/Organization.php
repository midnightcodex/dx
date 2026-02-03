<?php

namespace App\Modules\Shared\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Organization model - the tenant entity for multi-tenancy.
 * 
 * @property string $id
 * @property string $name
 * @property string $code
 * @property string|null $tax_id
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 * @property string $timezone
 * @property string $currency
 * @property bool $is_active
 */
class Organization extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'shared.organizations';

    protected $fillable = [
        'name',
        'code',
        'tax_id',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'phone',
        'email',
        'logo_url',
        'timezone',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all users belonging to this organization.
     */
    public function users()
    {
        return $this->hasMany(\App\Modules\Auth\Models\User::class, 'organization_id');
    }
}
