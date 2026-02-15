<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\Item;
use App\Modules\Inventory\Models\StockLedger;
use App\Modules\Inventory\Models\StockTransaction;

class ItemService
{
    public function list(string $organizationId, array $filters, int $perPage = 15)
    {
        $query = Item::with(['category', 'primaryUom'])
            ->where('organization_id', $organizationId);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('item_code', 'ilike', "%{$search}%");
            });
        }

        if (!empty($filters['type'])) {
            $query->where('item_type', $filters['type']);
        }

        return $query->paginate($perPage);
    }

    public function listActive(string $organizationId): array
    {
        return Item::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'item_code', 'name'])
            ->toArray();
    }

    public function find(string $organizationId, string $id): Item
    {
        return Item::with(['category', 'primaryUom'])
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
    }

    public function countActive(string $organizationId): int
    {
        return Item::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->count();
    }

    public function recentForDashboard(string $organizationId, int $limit = 5): array
    {
        return Item::with(['primaryUom', 'stockLedgers'])
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->take($limit)
            ->get()
            ->map(function (Item $item) {
                return [
                    'id' => $item->id,
                    'itemCode' => $item->item_code,
                    'name' => $item->name,
                    'warehouse' => 'Multiple',
                    'quantity' => $item->stockLedgers->sum('quantity_available'),
                    'unit' => $item->primaryUom?->symbol ?? 'N/A',
                    'reorderLevel' => $item->reorder_level ?? 0,
                ];
            })
            ->all();
    }

    public function create(string $organizationId, string $userId, array $data): Item
    {
        $data['organization_id'] = $organizationId;
        $data['created_by'] = $userId;

        return Item::create($data);
    }

    public function update(Item $item, string $userId, array $data): Item
    {
        $data['updated_by'] = $userId;
        $item->update($data);

        return $item->refresh();
    }

    public function delete(Item $item): void
    {
        $item->delete();
    }

    public function stockLevels(string $organizationId, string $itemId): array
    {
        $rows = StockLedger::query()
            ->where('organization_id', $organizationId)
            ->where('item_id', $itemId)
            ->get(['warehouse_id', 'batch_id', 'quantity_available', 'quantity_reserved', 'quantity_in_transit', 'unit_cost']);

        return $rows->map(fn($row) => [
            'warehouse_id' => $row->warehouse_id,
            'batch_id' => $row->batch_id,
            'quantity_available' => (float) $row->quantity_available,
            'quantity_reserved' => (float) $row->quantity_reserved,
            'quantity_in_transit' => (float) $row->quantity_in_transit,
            'net_available' => (float) $row->quantity_available - (float) $row->quantity_reserved,
            'unit_cost' => (float) $row->unit_cost,
        ])->all();
    }

    public function transactionHistory(string $organizationId, string $itemId, int $limit = 50): array
    {
        return StockTransaction::query()
            ->where('organization_id', $organizationId)
            ->where('item_id', $itemId)
            ->orderByDesc('transaction_date')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
