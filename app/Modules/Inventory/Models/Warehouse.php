<?php

namespace App\Modules\Inventory\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Warehouse model - storage locations.
 * Uses the 'inventory.warehouses' table in PostgreSQL.
 */
class Warehouse extends Model
{
    use HasUuid, BelongsToOrganization, SoftDeletes;

    protected $table = 'inventory.warehouses';

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'type',
        'address',
        'manager_id',
        'allow_negative_stock',
        'is_active',
    ];

    protected $casts = [
        'allow_negative_stock' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get stock ledger entries for this warehouse.
     */
    public function stockLedgers()
    {
        return $this->hasMany(StockLedger::class, 'warehouse_id');
    }

    /**
     * Get stock transactions for this warehouse.
     */
    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class, 'warehouse_id');
    }

    /**
     * Get the manager user.
     */
    public function manager()
    {
        return $this->belongsTo(\App\Modules\Auth\Models\User::class, 'manager_id');
    }
}
