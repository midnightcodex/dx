<?php

namespace Tests\Feature;

use App\Modules\Inventory\Models\StockLedger;
use App\Modules\Inventory\Models\StockTransaction;
use App\Modules\Inventory\Services\InventoryPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\BuildsErpContext;
use Tests\TestCase;

class SafetyAndGovernanceTest extends TestCase
{
    use RefreshDatabase;
    use BuildsErpContext;

    public function test_negative_stock_is_blocked_when_warehouse_disallows_it(): void
    {
        $org = $this->createOrganization();
        $user = $this->createSuperAdminUser($org);
        $this->actingAsJwt($user);

        $uom = $this->createUom($org);
        $warehouse = $this->createWarehouse($org, ['allow_negative_stock' => false, 'code' => 'NEG-01']);
        $item = $this->createItem($org, $uom, ['item_code' => 'NEG-ITEM-01']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Insufficient stock');

        app(InventoryPostingService::class)->post(
            transactionType: 'ISSUE',
            itemId: $item->id,
            warehouseId: $warehouse->id,
            quantity: -1,
            unitCost: 2,
            referenceType: 'TEST',
            referenceId: (string) Str::uuid(),
            organizationId: $org->id
        );
    }

    public function test_stock_posting_concurrency_simulation_preserves_balance_integrity(): void
    {
        $org = $this->createOrganization();
        $user = $this->createSuperAdminUser($org);
        $this->actingAsJwt($user);

        $uom = $this->createUom($org);
        $warehouse = $this->createWarehouse($org, ['code' => 'CON-01']);
        $item = $this->createItem($org, $uom, ['item_code' => 'CON-ITEM-01']);

        config(['concurrency.default' => 'sync']);

        $taskCount = 25;
        $tasks = [];
        for ($i = 0; $i < $taskCount; $i++) {
            $tasks[] = function () use ($org, $item, $warehouse) {
                return app(InventoryPostingService::class)->post(
                    transactionType: 'RECEIPT',
                    itemId: $item->id,
                    warehouseId: $warehouse->id,
                    quantity: 2,
                    unitCost: 1,
                    referenceType: 'CONCURRENCY_TEST',
                    referenceId: (string) Str::uuid(),
                    organizationId: $org->id
                )->id;
            };
        }

        $results = Concurrency::run($tasks);
        $this->assertCount($taskCount, $results);

        $ledger = StockLedger::query()
            ->where('organization_id', $org->id)
            ->where('item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->firstOrFail();
        $this->assertSame(50.0, (float) $ledger->quantity_available);

        $txCount = StockTransaction::query()
            ->where('organization_id', $org->id)
            ->where('item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->where('reference_type', 'CONCURRENCY_TEST')
            ->count();
        $this->assertSame($taskCount, $txCount);
    }

    public function test_permission_middleware_blocks_create_without_create_permission(): void
    {
        $org = $this->createOrganization();
        $user = $this->createScopedUserWithPermissions($org, 'inventory-viewer', ['inventory-view']);
        $this->actingAsJwt($user);

        $uom = $this->createUom($org);

        $this->getJson('/api/inventory/items')->assertOk();

        $this->postJson('/api/inventory/items', [
            'item_code' => 'PERM-ITEM-01',
            'name' => 'Permission Test Item',
            'primary_uom_id' => $uom->id,
            'item_type' => 'STOCKABLE',
            'stock_type' => 'RAW_MATERIAL',
            'is_batch_tracked' => false,
            'is_serial_tracked' => false,
        ])->assertStatus(403);
    }

    public function test_approval_workflow_progresses_step_by_step_until_approved(): void
    {
        $org = $this->createOrganization();
        $user = $this->createSuperAdminUser($org);
        $this->actingAsJwt($user);

        $workflow = $this->postJson('/api/shared/approval-workflows', [
            'workflow_name' => 'PO Approval Flow',
            'document_type' => 'PURCHASE_ORDER',
            'is_active' => true,
            'steps' => [
                ['step_number' => 1],
                ['step_number' => 2],
            ],
        ]);
        $workflow->assertCreated();

        $entityId = (string) Str::uuid();
        $request = $this->postJson('/api/shared/approval-requests', [
            'entity_type' => 'PURCHASE_ORDER',
            'entity_id' => $entityId,
            'from_status' => 'DRAFT',
            'to_status' => 'APPROVED',
            'amount' => 1000,
        ]);
        $request->assertCreated();

        $approvalId = $request->json('data.id');

        $stepOne = $this->postJson("/api/shared/approval-requests/{$approvalId}/approve");
        $stepOne->assertOk();
        $stepOne->assertJsonPath('data.status', 'PENDING');
        $stepOne->assertJsonPath('data.current_step', 2);

        $stepTwo = $this->postJson("/api/shared/approval-requests/{$approvalId}/approve");
        $stepTwo->assertOk();
        $stepTwo->assertJsonPath('data.status', 'APPROVED');
    }

    public function test_no_direct_stock_ledger_writes_outside_inventory_posting_service(): void
    {
        $servicePath = realpath(app_path('Modules/Inventory/Services/InventoryPostingService.php'));
        $violations = [];

        $patterns = [
            '/StockLedger::\s*(create|forceCreate|updateOrCreate|firstOrNew|upsert|insert)\s*\(/',
            '/StockLedger::[^\n;]*->\s*(update|delete|insert|upsert)\s*\(/',
            '/DB::table\(\s*[\'"]inventory\.stock_ledger[\'"]\s*\)[^\n;]*->\s*(insert|update|delete|upsert)\s*\(/',
        ];

        foreach (File::allFiles(app_path()) as $file) {
            $path = $file->getRealPath();
            if ($path === $servicePath) {
                continue;
            }

            $contents = File::get($path);
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $contents)) {
                    $violations[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
                    break;
                }
            }
        }

        $this->assertSame([], $violations, 'Direct stock ledger writes detected outside InventoryPostingService.');
    }
}
