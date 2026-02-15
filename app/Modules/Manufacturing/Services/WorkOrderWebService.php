<?php

namespace App\Modules\Manufacturing\Services;

use App\Modules\Inventory\Services\ItemService;
use App\Modules\Inventory\Services\WarehouseService;
use App\Modules\Manufacturing\Models\BomHeader;
use App\Modules\Manufacturing\Models\WorkOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WorkOrderWebService
{
    public function __construct(
        private ItemService $itemService,
        private WarehouseService $warehouseService
    ) {
    }

    public function list(string $organizationId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = WorkOrder::with(['item', 'sourceWarehouse', 'targetWarehouse', 'bom'])
            ->where('organization_id', $organizationId);

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', strtoupper($filters['status']));
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('wo_number', 'ilike', "%{$search}%")
                    ->orWhereHas('item', fn($q) => $q->where('name', 'ilike', "%{$search}%"));
            });
        }

        $workOrders = $query->latest()->paginate($perPage);

        $workOrders->getCollection()->transform(function ($wo) {
            return [
                'id' => $wo->id,
                'woNumber' => $wo->wo_number,
                'product' => $wo->item?->name ?? 'N/A',
                'quantity' => $wo->planned_quantity,
                'completedQuantity' => $wo->completed_quantity ?? 0,
                'status' => ucwords(strtolower(str_replace('_', ' ', $wo->status))),
                'rawStatus' => $wo->status,
                'scheduledStart' => $wo->scheduled_start_date?->format('Y-m-d'),
                'scheduledEnd' => $wo->scheduled_end_date?->format('Y-m-d'),
                'sourceWarehouse' => $wo->sourceWarehouse?->name ?? 'N/A',
                'targetWarehouse' => $wo->targetWarehouse?->name ?? 'N/A',
            ];
        });

        return $workOrders;
    }

    public function getCreateData(string $organizationId): array
    {
        $itemIds = BomHeader::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->pluck('item_id')
            ->unique()
            ->values()
            ->all();

        $items = collect($this->itemService->listActive($organizationId))
            ->whereIn('id', $itemIds)
            ->values();

        $itemsById = $items->keyBy('id');

        $boms = BomHeader::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->get(['id', 'bom_number', 'item_id', 'version'])
            ->map(function ($bom) {
                return [
                    'id' => $bom->id,
                    'item_id' => $bom->item_id,
                    'bom_code' => $bom->bom_number,
                    'revision' => $bom->version,
                ];
            });

        $boms = $boms->map(function (array $bom) use ($itemsById) {
            $item = $itemsById->get($bom['item_id']);
            $bom['item'] = $item ? [
                'id' => $item['id'],
                'name' => $item['name'],
            ] : null;

            return $bom;
        });

        $warehouses = $this->warehouseService->listActive($organizationId);

        return [
            'items' => $items,
            'boms' => $boms,
            'warehouses' => $warehouses,
        ];
    }

    public function create(string $organizationId, string $userId, array $data): WorkOrder
    {
        $data['organization_id'] = $organizationId;
        $data['created_by'] = $userId;
        $data['wo_number'] = $this->generateWoNumber();
        $data['status'] = WorkOrder::STATUS_PLANNED;
        $data['priority'] = $this->normalizePriority($data['priority'] ?? null);

        return WorkOrder::create($data);
    }

    public function find(string $organizationId, string $id): WorkOrder
    {
        return WorkOrder::with([
            'item',
            'bom.lines.componentItem',
            'materials.item',
            'operations',
            'sourceWarehouse',
            'targetWarehouse',
            'createdBy',
        ])
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
    }

    public function release(string $organizationId, string $userId, string $id): ?WorkOrder
    {
        $workOrder = WorkOrder::where('organization_id', $organizationId)->findOrFail($id);

        if ($workOrder->status !== WorkOrder::STATUS_PLANNED) {
            return null;
        }

        $workOrder->update([
            'status' => WorkOrder::STATUS_RELEASED,
            'updated_by' => $userId,
        ]);

        return $workOrder->refresh();
    }

    public function start(string $organizationId, string $userId, string $id): ?WorkOrder
    {
        $workOrder = WorkOrder::where('organization_id', $organizationId)->findOrFail($id);

        if ($workOrder->status !== WorkOrder::STATUS_RELEASED) {
            return null;
        }

        $workOrder->update([
            'status' => WorkOrder::STATUS_IN_PROGRESS,
            'actual_start_at' => now(),
            'updated_by' => $userId,
        ]);

        return $workOrder->refresh();
    }

    private function generateWoNumber(): string
    {
        $prefix = 'WO';
        $year = date('Y');
        $month = date('m');

        $lastWo = WorkOrder::where('wo_number', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderBy('wo_number', 'desc')
            ->first();

        if ($lastWo) {
            $lastNumber = (int) substr($lastWo->wo_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}-{$year}{$month}-{$nextNumber}";
    }

    private function normalizePriority($value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        $map = [
            'LOW' => 1,
            'NORMAL' => 5,
            'HIGH' => 8,
            'URGENT' => 10,
        ];

        $key = strtoupper((string) $value);

        return $map[$key] ?? 5;
    }
}
