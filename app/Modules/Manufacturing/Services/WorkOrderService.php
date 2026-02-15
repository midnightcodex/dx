<?php

namespace App\Modules\Manufacturing\Services;

use App\Modules\Manufacturing\Models\BomHeader;
use App\Modules\Manufacturing\Models\WorkOrderMaterial;
use App\Modules\Manufacturing\Models\WorkOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkOrderService
{
    public function list(string $organizationId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = WorkOrder::with(['item', 'bom', 'sourceWarehouse'])
            ->where('organization_id', $organizationId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(string $organizationId, string $id): WorkOrder
    {
        return WorkOrder::with(['materials', 'operations', 'item'])
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
    }

    public function countActive(string $organizationId): int
    {
        return WorkOrder::where('organization_id', $organizationId)
            ->whereIn('status', [WorkOrder::STATUS_RELEASED, WorkOrder::STATUS_IN_PROGRESS])
            ->count();
    }

    public function recentForDashboard(string $organizationId, int $limit = 5): array
    {
        return WorkOrder::with('item')
            ->where('organization_id', $organizationId)
            ->latest()
            ->take($limit)
            ->get()
            ->map(function (WorkOrder $wo) {
                return [
                    'id' => $wo->id,
                    'woNumber' => $wo->wo_number,
                    'product' => $wo->item?->name ?? 'N/A',
                    'quantity' => $wo->planned_quantity,
                    'status' => ucwords(strtolower(str_replace('_', ' ', $wo->status))),
                    'startDate' => $wo->scheduled_start_date ? $wo->scheduled_start_date->format('Y-m-d') : 'TBD',
                ];
            })
            ->all();
    }

    public function create(string $organizationId, string $userId, array $data): WorkOrder
    {
        $data['organization_id'] = $organizationId;
        $data['created_by'] = $userId;
        $data['wo_number'] = $this->generateNumber();
        $data['status'] = WorkOrder::STATUS_PLANNED;

        return WorkOrder::create($data);
    }

    public function update(WorkOrder $workOrder, string $userId, array $data): WorkOrder
    {
        $data['updated_by'] = $userId;
        $workOrder->update($data);

        return $workOrder->refresh();
    }

    public function release(string $organizationId, string $userId, string $id): ?WorkOrder
    {
        return DB::transaction(function () use ($organizationId, $userId, $id) {
            $workOrder = WorkOrder::where('organization_id', $organizationId)
                ->findOrFail($id);

            if ($workOrder->status !== WorkOrder::STATUS_PLANNED) {
                return null;
            }

            $workOrder->update([
                'status' => WorkOrder::STATUS_RELEASED,
                'updated_by' => $userId,
            ]);

            // Generate WO material lines from BOM on release if not yet created.
            if ($workOrder->materials()->count() === 0 && $workOrder->bom_id) {
                $bom = BomHeader::query()
                    ->with('lines')
                    ->where('organization_id', $organizationId)
                    ->find($workOrder->bom_id);

                if ($bom) {
                    foreach ($bom->lines as $line) {
                        $required = ((float) $line->quantity_per_unit * (float) $workOrder->planned_quantity)
                            / max(0.0001, (float) $bom->base_quantity);

                        WorkOrderMaterial::create([
                            'organization_id' => $organizationId,
                            'work_order_id' => $workOrder->id,
                            'item_id' => $line->component_item_id,
                            'required_quantity' => $required,
                            'warehouse_id' => $workOrder->source_warehouse_id,
                            'operation_sequence' => $line->operation_sequence,
                            'status' => 'PENDING',
                        ]);
                    }
                }
            }

            return $workOrder->refresh();
        });
    }

    private function generateNumber(): string
    {
        return 'WO-' . Str::upper(uniqid());
    }
}
