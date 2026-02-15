<?php

namespace App\Modules\Manufacturing\Services;

use App\Modules\Inventory\Models\StockLedger;
use App\Modules\Inventory\Services\InventoryPostingService;
use App\Modules\Manufacturing\Models\ScrapEntry;
use App\Modules\Manufacturing\Models\ScrapRecovery;
use App\Modules\Shared\Services\NumberSeriesService;
use Illuminate\Support\Facades\DB;

class ScrapService
{
    public function __construct(
        private NumberSeriesService $numberSeriesService,
        private InventoryPostingService $inventoryPostingService
    ) {
    }

    public function list(string $organizationId, int $perPage = 15)
    {
        return ScrapEntry::query()
            ->with('recovery')
            ->where('organization_id', $organizationId)
            ->latest()
            ->paginate($perPage);
    }

    public function create(string $organizationId, string $userId, array $data): ScrapEntry
    {
        return DB::transaction(function () use ($organizationId, $userId, $data) {
            $ledger = StockLedger::query()
                ->where('organization_id', $organizationId)
                ->where('item_id', $data['item_id'])
                ->where('warehouse_id', $data['warehouse_id'])
                ->where('batch_id', $data['batch_id'] ?? null)
                ->first();

            $transaction = $this->inventoryPostingService->post(
                transactionType: 'SCRAP',
                itemId: $data['item_id'],
                warehouseId: $data['warehouse_id'],
                quantity: -abs((float) $data['scrap_quantity']),
                unitCost: (float) ($ledger?->unit_cost ?? 0),
                referenceType: $data['source_type'] ?? 'SCRAP',
                referenceId: $data['source_id'] ?? $data['item_id'],
                batchId: $data['batch_id'] ?? null,
                organizationId: $organizationId
            );

            return ScrapEntry::create([
                'organization_id' => $organizationId,
                'scrap_number' => $data['scrap_number'] ?? $this->numberSeriesService->next(
                    $organizationId,
                    'SCRAP',
                    ['prefix' => 'SCR-', 'padding' => 5]
                ),
                'source_type' => $data['source_type'] ?? null,
                'source_id' => $data['source_id'] ?? null,
                'item_id' => $data['item_id'],
                'scrap_quantity' => abs((float) $data['scrap_quantity']),
                'scrap_value' => (float) ($data['scrap_value'] ?? 0),
                'scrap_reason' => $data['scrap_reason'] ?? null,
                'scrap_category' => $data['scrap_category'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'batch_id' => $data['batch_id'] ?? null,
                'disposal_method' => $data['disposal_method'] ?? null,
                'disposed_quantity' => (float) ($data['disposed_quantity'] ?? 0),
                'disposal_date' => $data['disposal_date'] ?? null,
                'recorded_by' => $userId,
                'inventory_transaction_id' => $transaction->id,
            ]);
        });
    }

    public function dispose(string $organizationId, string $userId, string $scrapId, array $data): ScrapEntry
    {
        $scrap = ScrapEntry::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($scrapId);

        $scrap->disposal_method = $data['disposal_method'] ?? $scrap->disposal_method;
        $scrap->disposed_quantity = (float) ($data['disposed_quantity'] ?? $scrap->disposed_quantity);
        $scrap->disposal_date = $data['disposal_date'] ?? now()->toDateString();
        $scrap->save();

        return $scrap->refresh();
    }

    public function recover(string $organizationId, string $userId, string $scrapId, array $data): ScrapRecovery
    {
        $scrap = ScrapEntry::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($scrapId);

        $recovery = ScrapRecovery::create([
            'scrap_entry_id' => $scrap->id,
            'recovered_item_id' => $data['recovered_item_id'] ?? null,
            'recovered_quantity' => (float) $data['recovered_quantity'],
            'recovery_value' => (float) ($data['recovery_value'] ?? 0),
            'recovery_date' => $data['recovery_date'] ?? now()->toDateString(),
            'sold_to' => $data['sold_to'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return $recovery;
    }
}
