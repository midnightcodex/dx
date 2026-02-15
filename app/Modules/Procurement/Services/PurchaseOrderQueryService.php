<?php

namespace App\Modules\Procurement\Services;

use App\Modules\Procurement\Models\PurchaseOrder;

class PurchaseOrderQueryService
{
    public function countPending(string $organizationId): int
    {
        return PurchaseOrder::where('organization_id', $organizationId)
            ->where('status', PurchaseOrder::STATUS_SUBMITTED)
            ->count();
    }
}
