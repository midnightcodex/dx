<?php

namespace App\Modules\Shared\Services;

use App\Modules\Inventory\Services\ItemService;
use App\Modules\Manufacturing\Services\WorkOrderService;
use App\Modules\Procurement\Services\PurchaseOrderQueryService;

class DashboardService
{
    public function __construct(
        private ItemService $itemService,
        private WorkOrderService $workOrderService,
        private PurchaseOrderQueryService $purchaseOrderQueryService
    ) {
    }

    public function build(string $organizationId): array
    {
        // 1. Manufacturing Stats
        $activeWorkOrders = $this->workOrderService->countActive($organizationId);
        $recentWorkOrders = $this->workOrderService->recentForDashboard($organizationId, 5);

        // 2. Inventory Stats
        $totalItems = $this->itemService->countActive($organizationId);

        $lowStockItems = 0; // Requires stock ledger query, simplifying for now

        $recentItems = $this->itemService->recentForDashboard($organizationId, 5);

        // 3. Procurement Stats
        $pendingPOs = $this->purchaseOrderQueryService->countPending($organizationId);

        // 4. Quality (Placeholder for now)
        $qualityIssues = 0;

        return [
            'stats' => [
                'activeWorkOrders' => $activeWorkOrders,
                'totalItems' => $totalItems,
                'pendingPOs' => $pendingPOs,
                'qualityIssues' => $qualityIssues,
            ],
            'tables' => [
                'workOrders' => $recentWorkOrders,
                'inventory' => $recentItems,
            ],
        ];
    }
}
