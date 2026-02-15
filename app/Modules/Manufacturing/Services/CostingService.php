<?php

namespace App\Modules\Manufacturing\Services;

use App\Modules\Manufacturing\Models\WorkOrder;
use App\Modules\Manufacturing\Models\WorkOrderCost;
use Illuminate\Support\Facades\DB;

class CostingService
{
    public function calculateForWorkOrder(string $organizationId, string $workOrderId): array
    {
        $workOrder = WorkOrder::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($workOrderId);

        $materialActual = (float) DB::table('inventory.stock_transactions')
            ->where('organization_id', $organizationId)
            ->where('reference_type', 'WORK_ORDER')
            ->where('reference_id', $workOrder->id)
            ->where('transaction_type', 'PRODUCTION_ISSUE')
            ->selectRaw('COALESCE(SUM(ABS(total_value)), 0) as total')
            ->value('total');

        $materialStandard = (float) DB::table('manufacturing.work_order_materials')
            ->where('organization_id', $organizationId)
            ->where('work_order_id', $workOrder->id)
            ->join('inventory.items', 'inventory.items.id', '=', 'manufacturing.work_order_materials.item_id')
            ->selectRaw('COALESCE(SUM(manufacturing.work_order_materials.required_quantity * COALESCE(inventory.items.standard_cost,0)), 0) as total')
            ->value('total');

        $laborHours = (float) DB::table('manufacturing.production_logs')
            ->where('organization_id', $organizationId)
            ->where('work_order_id', $workOrder->id)
            ->count();
        $laborRate = 0.0;
        $laborActual = $laborHours * $laborRate;

        $summary = [
            'material' => $this->upsertCost($organizationId, $workOrder->id, 'MATERIAL', $materialStandard, $materialActual, $laborHours, 0),
            'labor' => $this->upsertCost($organizationId, $workOrder->id, 'LABOR', 0, $laborActual, $laborHours, $laborRate),
            'total_standard' => $materialStandard,
            'total_actual' => $materialActual + $laborActual,
            'total_variance' => ($materialActual + $laborActual) - $materialStandard,
        ];

        return $summary;
    }

    public function varianceAnalysis(string $organizationId): array
    {
        return WorkOrderCost::query()
            ->where('organization_id', $organizationId)
            ->selectRaw('cost_type, SUM(standard_cost) as standard_cost, SUM(actual_cost) as actual_cost, SUM(variance) as variance')
            ->groupBy('cost_type')
            ->get()
            ->toArray();
    }

    private function upsertCost(
        string $organizationId,
        string $workOrderId,
        string $costType,
        float $standard,
        float $actual,
        float $quantity,
        float $rate
    ): WorkOrderCost {
        $cost = WorkOrderCost::query()
            ->where('organization_id', $organizationId)
            ->where('work_order_id', $workOrderId)
            ->where('cost_type', $costType)
            ->whereNull('cost_center_id')
            ->first();

        if (!$cost) {
            $cost = new WorkOrderCost([
                'organization_id' => $organizationId,
                'work_order_id' => $workOrderId,
                'cost_type' => $costType,
                'cost_center_id' => null,
            ]);
        }

        $cost->standard_cost = $standard;
        $cost->actual_cost = $actual;
        $cost->variance = $actual - $standard;
        $cost->quantity = $quantity;
        $cost->rate = $rate;
        $cost->calculation_date = now();
        $cost->save();

        return $cost;
    }
}
