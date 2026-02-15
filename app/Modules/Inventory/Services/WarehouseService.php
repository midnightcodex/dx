<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Support\Collection;

class WarehouseService
{
    public function listActive(string $organizationId): Collection
    {
        return Warehouse::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);
    }
}
