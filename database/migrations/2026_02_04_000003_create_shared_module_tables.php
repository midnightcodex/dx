<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Shared module tables - common entities used across all modules.
     * UOM, Categories, Number Series, System Settings, Status Transitions.
     */
    public function up(): void
    {
        // Units of Measure
        Schema::create('shared.uom', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('name', 50);           // e.g., 'Kilogram', 'Piece'
            $table->string('symbol', 10);          // e.g., 'kg', 'pc'
            $table->string('category', 50);        // 'weight', 'length', 'quantity', 'volume'
            $table->decimal('conversion_factor', 15, 6)->default(1); // For base unit conversion
            $table->uuid('base_uom_id')->nullable(); // Reference to base unit
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['organization_id', 'symbol']);
            $table->index('organization_id');
        });

        // Item Categories (hierarchical)
        Schema::create('shared.item_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('parent_id')->nullable();
            $table->string('name', 100);
            $table->string('code', 50);
            $table->text('description')->nullable();
            $table->string('type', 50)->default('GENERAL'); // 'RAW_MATERIAL', 'FINISHED_GOOD', 'WIP', 'CONSUMABLE'
            $table->integer('level')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['organization_id', 'code']);
            $table->index(['organization_id', 'parent_id']);
            $table->index(['organization_id', 'type']);
        });

        // Number Series (auto-numbering for documents)
        Schema::create('shared.number_series', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('entity_type', 100);    // 'WORK_ORDER', 'PURCHASE_ORDER', 'GRN', etc.
            $table->string('prefix', 20);          // e.g., 'WO-', 'PO-'
            $table->string('suffix', 20)->nullable();
            $table->string('format', 100);         // e.g., '{PREFIX}{YYMMDD}-{NNNNNN}'
            $table->integer('current_number')->default(0);
            $table->integer('padding')->default(6); // Number of digits
            $table->boolean('include_date')->default(true);
            $table->string('date_format', 20)->default('YYMMDD');
            $table->boolean('reset_on_date_change')->default(false);
            $table->date('last_reset_date')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'entity_type']);
        });

        // System Settings (key-value configuration)
        Schema::create('shared.system_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('category', 50);        // 'inventory', 'manufacturing', 'system'
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->string('value_type', 20)->default('string'); // 'string', 'integer', 'boolean', 'json'
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // Can be shown in UI
            $table->timestamps();

            $table->unique(['organization_id', 'category', 'key']);
        });

        // Status Transitions (state machine rules)
        Schema::create('shared.status_transitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('entity_type', 100);    // 'WORK_ORDER', 'PURCHASE_ORDER', etc.
            $table->string('from_status', 50);
            $table->string('to_status', 50);
            $table->boolean('is_allowed')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->string('approval_role', 50)->nullable();
            $table->text('condition')->nullable(); // Optional JSON condition
            $table->timestamps();

            $table->unique(['entity_type', 'from_status', 'to_status']);
            $table->index('entity_type');
        });

        // Approval Requests (workflow queue)
        Schema::create('shared.approval_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('entity_type', 100);
            $table->uuid('entity_id');
            $table->string('from_status', 50);
            $table->string('to_status', 50);
            $table->integer('current_step')->default(1);
            $table->integer('total_steps')->default(1);
            $table->string('status', 20)->default('PENDING'); // 'PENDING', 'APPROVED', 'REJECTED'
            $table->uuid('requested_by');
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared.approval_requests');
        Schema::dropIfExists('shared.status_transitions');
        Schema::dropIfExists('shared.system_settings');
        Schema::dropIfExists('shared.number_series');
        Schema::dropIfExists('shared.item_categories');
        Schema::dropIfExists('shared.uom');
    }
};
