<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Shared\Models\Uom;
use Illuminate\Support\Collection;

class UomService
{
    public function listActive(string $organizationId): Collection
    {
        return Uom::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->orderBy('symbol')
            ->get(['id', 'symbol', 'name'])
            ->map(fn($uom) => [
                'id' => $uom->id,
                'uom_code' => $uom->symbol,
                'uom_name' => $uom->name,
                'symbol' => $uom->symbol,
                'name' => $uom->name,
            ]);
    }
}
