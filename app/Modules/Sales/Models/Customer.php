<?php

namespace App\Modules\Sales\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasUuid, BelongsToOrganization, HasAuditFields, SoftDeletes;

    protected $table = 'sales.customers';

    protected $fillable = [
        'organization_id',
        'customer_code',
        'name',
        'email',
        'phone',
        'tax_id',
        'billing_address',
        'shipping_address',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
