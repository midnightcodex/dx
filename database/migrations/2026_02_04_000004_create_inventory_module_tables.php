<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Inventory module tables - warehouses, items, stock ledger, stock transactions.
     * Implements the LEDGER PATTERN as specified in docs.md Section 3.2.
     */
    public function up(): void
    {
        // Warehouses / Storage Locations
        Schema::create('inventory.warehouses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('name', 100);
            $table->string('code', 50);
            $table->string('type', 50)->default('WAREHOUSE'); // 'WAREHOUSE', 'SHOP_FLOOR', 'TRANSIT', 'QUARANTINE'
            $table->text('address')->nullable();
            $table->uuid('manager_id')->nullable();
            $table->boolean('allow_negative_stock')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'code']);
            $table->index('organization_id');
        });

        // Items / Products / Materials
        Schema::create('inventory.items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('item_code', 100);
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->uuid('category_id')->nullable();
            $table->uuid('primary_uom_id');
            $table->string('item_type', 50)->default('STOCKABLE'); // 'STOCKABLE', 'SERVICE', 'CONSUMABLE'
            $table->string('stock_type', 50)->default('RAW_MATERIAL'); // 'RAW_MATERIAL', 'WIP', 'FINISHED_GOOD', 'SPARE_PART'
            $table->boolean('is_batch_tracked')->default(false);
            $table->boolean('is_serial_tracked')->default(false);
            $table->decimal('reorder_level', 15, 4)->nullable();
            $table->decimal('reorder_quantity', 15, 4)->nullable();
            $table->decimal('safety_stock', 15, 4)->nullable();
            $table->decimal('lead_time_days', 5, 1)->nullable();
            $table->decimal('standard_cost', 15, 4)->nullable();
            $table->string('hs_code', 20)->nullable();       // For customs/export
            $table->string('barcode', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'item_code']);
            $table->index(['organization_id', 'category_id']);
            $table->index(['organization_id', 'stock_type']);
            $table->index(['organization_id', 'is_active']);
        });

        // Stock Ledger - THE TRUTH (current stock state)
        // CRITICAL: Only InventoryPostingService can write to this table!
        Schema::create('inventory.stock_ledger', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('item_id');
            $table->uuid('warehouse_id');
            $table->uuid('batch_id')->nullable();
            $table->decimal('quantity_available', 15, 4)->default(0);
            $table->decimal('quantity_reserved', 15, 4)->default(0);
            $table->decimal('quantity_in_transit', 15, 4)->default(0);
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->uuid('last_transaction_id')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'item_id', 'warehouse_id', 'batch_id'], 'stock_ledger_unique');
            $table->index(['organization_id', 'item_id', 'warehouse_id'], 'stock_ledger_lookup');
            $table->index(['organization_id', 'warehouse_id']);
        });

        // Stock Transactions - THE FACTS (immutable event log)
        // CRITICAL: Append-only! Never UPDATE or DELETE!
        Schema::create('inventory.stock_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('transaction_type', 50); // 'RECEIPT', 'ISSUE', 'ADJUSTMENT', 'TRANSFER'
            $table->uuid('item_id');
            $table->uuid('warehouse_id');
            $table->uuid('batch_id')->nullable();
            $table->decimal('quantity', 15, 4);     // Positive for receipts, negative for issues
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_value', 15, 4)->default(0);
            $table->string('reference_type', 100);  // 'GRN', 'WORK_ORDER', 'SALES_ORDER', 'ADJUSTMENT'
            $table->uuid('reference_id');
            $table->decimal('balance_after', 15, 4)->default(0); // Snapshot of balance after this transaction
            $table->boolean('is_cancelled')->default(false);
            $table->text('cancelled_reason')->nullable();
            $table->uuid('created_by');
            $table->timestamp('transaction_date');
            $table->timestamps();

            $table->index(['organization_id', 'item_id', 'warehouse_id']);
            $table->index(['organization_id', 'reference_type', 'reference_id']);
            $table->index(['organization_id', 'transaction_date']);
            $table->index(['organization_id', 'transaction_type']);
        });

        // Batches (for batch-tracked items)
        Schema::create('inventory.batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('item_id');
            $table->string('batch_number', 100);
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('supplier_batch', 100)->nullable();
            $table->uuid('vendor_id')->nullable();
            $table->string('status', 20)->default('ACTIVE'); // 'ACTIVE', 'QUARANTINE', 'EXPIRED', 'CONSUMED'
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'item_id', 'batch_number']);
            $table->index(['organization_id', 'item_id']);
            $table->index(['organization_id', 'expiry_date']);
        });

        // Batch Movements (traceability view)
        Schema::create('inventory.batch_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('batch_id');
            $table->uuid('stock_transaction_id');
            $table->uuid('from_warehouse_id')->nullable();
            $table->uuid('to_warehouse_id')->nullable();
            $table->decimal('quantity', 15, 4);
            $table->string('movement_type', 50);
            $table->timestamps();

            $table->index(['organization_id', 'batch_id']);
            $table->foreign('stock_transaction_id')->references('id')->on('inventory.stock_transactions');
        });

        // Stock Adjustments (document for controlled corrections)
        Schema::create('inventory.stock_adjustments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('adjustment_number', 50);
            $table->uuid('warehouse_id');
            $table->string('adjustment_type', 50); // 'CYCLE_COUNT', 'DAMAGED', 'WRITE_OFF', 'CORRECTION'
            $table->string('status', 20)->default('DRAFT'); // 'DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'POSTED'
            $table->text('reason');
            $table->uuid('created_by');
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'adjustment_number']);
            $table->index(['organization_id', 'status']);
        });

        // Stock Adjustment Lines
        Schema::create('inventory.stock_adjustment_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('stock_adjustment_id');
            $table->uuid('item_id');
            $table->uuid('batch_id')->nullable();
            $table->decimal('system_quantity', 15, 4);
            $table->decimal('actual_quantity', 15, 4);
            $table->decimal('difference', 15, 4);
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('stock_adjustment_id')->references('id')->on('inventory.stock_adjustments')->onDelete('cascade');
            $table->index(['organization_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory.stock_adjustment_lines');
        Schema::dropIfExists('inventory.stock_adjustments');
        Schema::dropIfExists('inventory.batch_movements');
        Schema::dropIfExists('inventory.batches');
        Schema::dropIfExists('inventory.stock_transactions');
        Schema::dropIfExists('inventory.stock_ledger');
        Schema::dropIfExists('inventory.items');
        Schema::dropIfExists('inventory.warehouses');
    }
};
