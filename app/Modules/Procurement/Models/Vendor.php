<?php

namespace App\Modules\Procurement\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Vendor model - suppliers.
 * Uses the 'procurement.vendors' table in PostgreSQL.
 */
class Vendor extends Model
{
    use HasUuid, BelongsToOrganization, HasAuditFields, SoftDeletes;

    protected $table = 'procurement.vendors';

    protected $fillable = [
        'organization_id',
        'vendor_code',
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'tax_id',
        'payment_terms',
        'currency',
        'credit_limit',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    /**
     * Get purchase orders from this vendor.
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'vendor_id');
    }

    /**
     * Scope for active vendors.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }
}
