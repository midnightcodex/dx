<?php

namespace App\Modules\Manufacturing\Services;

use App\Modules\Manufacturing\Models\BomHeader;
use Illuminate\Support\Collection;

class BomService
{
    public function listActive(string $organizationId): Collection
    {
        return BomHeader::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->orderBy('bom_number')
            ->get(['id', 'item_id', 'bom_number', 'version'])
            ->map(function ($bom) {
                return [
                    'id' => $bom->id,
                    'item_id' => $bom->item_id,
                    'bom_code' => $bom->bom_number,
                    'revision' => $bom->version,
                ];
            });
    }
}
