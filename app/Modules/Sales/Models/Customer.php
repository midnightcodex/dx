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
        'name',
        'email',
        'phone',
        'billing_address',
        'shipping_address',
        'tax_id',
        'payment_terms',
        'currency',
        'is_active',
        'created_by',
        'updated_by',
    ];

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'customer_id');
    }
}
