<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Manufacturing module tables - BOM, Routings, Work Orders, Production.
     */
    public function up(): void
    {
        $useForeignKeys = DB::getDriverName() !== 'sqlite';

        // Work Centers (machines/stations)
        Schema::create('manufacturing.work_centers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('name', 100);
            $table->string('code', 50);
            $table->text('description')->nullable();
            $table->string('type', 50)->default('MACHINE'); // 'MACHINE', 'WORKSTATION', 'ASSEMBLY_LINE'
            $table->decimal('hourly_rate', 15, 2)->default(0);
            $table->decimal('capacity_per_hour', 15, 4)->nullable();
            $table->uuid('warehouse_id')->nullable(); // Associated warehouse
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'code']);
            $table->index('organization_id');
        });

        // Routings (manufacturing process templates)
        Schema::create('manufacturing.routings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('routing_number', 50);
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'routing_number']);
        });

        // Routing Operations (steps in a routing)
        Schema::create('manufacturing.routing_operations', function (Blueprint $table) use ($useForeignKeys) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('routing_id');
            $table->integer('sequence');
            $table->string('operation_name', 255);
            $table->uuid('work_center_id')->nullable();
            $table->integer('setup_time_minutes')->default(0);
            $table->decimal('run_time_per_unit', 10, 2)->default(0); // minutes
            $table->decimal('labor_hours_per_unit', 10, 4)->default(0);
            $table->boolean('quality_check_required')->default(false);
            $table->text('instructions')->nullable();
            $table->timestamps();

            if ($useForeignKeys) {
                $table->foreign('routing_id')->references('id')->on('manufacturing.routings')->onDelete('cascade');
            }
            $table->unique(['routing_id', 'sequence']);
        });

        // BOM Header (Bill of Materials)
        Schema::create('manufacturing.bom_headers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('item_id');              // Finished good
            $table->string('bom_number', 50);
            $table->integer('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->decimal('base_quantity', 15, 4)->default(1); // Quantity this BOM produces
            $table->uuid('uom_id')->nullable();
            $table->uuid('routing_id')->nullable();
            $table->decimal('estimated_cost', 15, 4)->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'bom_number', 'version']);
            $table->index(['organization_id', 'item_id', 'is_active']);
        });

        // BOM Lines (components)
        Schema::create('manufacturing.bom_lines', function (Blueprint $table) use ($useForeignKeys) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('bom_header_id');
            $table->integer('line_number');
            $table->uuid('component_item_id');
            $table->decimal('quantity_per_unit', 15, 6);
            $table->uuid('uom_id')->nullable();
            $table->decimal('scrap_percentage', 5, 2)->default(0);
            $table->integer('operation_sequence')->nullable(); // Which step needs this
            $table->boolean('is_critical')->default(false);
            $table->uuid('substitute_item_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            if ($useForeignKeys) {
                $table->foreign('bom_header_id')->references('id')->on('manufacturing.bom_headers')->onDelete('cascade');
            }
            $table->unique(['bom_header_id', 'line_number']);
            $table->index(['organization_id', 'component_item_id']);
        });

        // Work Orders
        Schema::create('manufacturing.work_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('wo_number', 50);
            $table->uuid('item_id');             // Product to manufacture
            $table->uuid('bom_id')->nullable();
            $table->uuid('routing_id')->nullable();
            $table->decimal('planned_quantity', 15, 4);
            $table->decimal('completed_quantity', 15, 4)->default(0);
            $table->decimal('rejected_quantity', 15, 4)->default(0);
            $table->uuid('source_warehouse_id');  // Raw material source
            $table->uuid('target_warehouse_id');  // Finished goods destination
            $table->string('status', 20)->default('PLANNED'); // 'PLANNED', 'RELEASED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'
            $table->date('scheduled_start_date')->nullable();
            $table->date('scheduled_end_date')->nullable();
            $table->timestamp('actual_start_at')->nullable();
            $table->timestamp('actual_end_at')->nullable();
            $table->integer('priority')->default(5);
            $table->uuid('production_plan_id')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'wo_number']);
            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'scheduled_start_date']);
        });

        // Work Order Materials (required components)
        Schema::create('manufacturing.work_order_materials', function (Blueprint $table) use ($useForeignKeys) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('work_order_id');
            $table->uuid('item_id');
            $table->uuid('batch_id')->nullable();
            $table->decimal('required_quantity', 15, 4);
            $table->decimal('issued_quantity', 15, 4)->default(0);
            $table->decimal('consumed_quantity', 15, 4)->default(0);
            $table->decimal('returned_quantity', 15, 4)->default(0);
            $table->uuid('warehouse_id');
            $table->integer('operation_sequence')->nullable();
            $table->string('status', 20)->default('PENDING'); // 'PENDING', 'ISSUED', 'CONSUMED'
            $table->timestamps();

            if ($useForeignKeys) {
                $table->foreign('work_order_id')->references('id')->on('manufacturing.work_orders')->onDelete('cascade');
            }
            $table->index(['organization_id', 'item_id']);
        });

        // Work Order Operations (steps to complete)
        Schema::create('manufacturing.work_order_operations', function (Blueprint $table) use ($useForeignKeys) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('work_order_id');
            $table->integer('sequence');
            $table->string('operation_name', 255);
            $table->uuid('work_center_id')->nullable();
            $table->decimal('planned_time_minutes', 10, 2)->default(0);
            $table->decimal('actual_time_minutes', 10, 2)->default(0);
            $table->decimal('setup_time_minutes', 10, 2)->default(0);
            $table->string('status', 20)->default('PENDING'); // 'PENDING', 'IN_PROGRESS', 'COMPLETED', 'SKIPPED'
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->uuid('completed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            if ($useForeignKeys) {
                $table->foreign('work_order_id')->references('id')->on('manufacturing.work_orders')->onDelete('cascade');
            }
            $table->unique(['work_order_id', 'sequence']);
        });

        // Production Logs (actual work recorded)
        Schema::create('manufacturing.production_logs', function (Blueprint $table) use ($useForeignKeys) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('work_order_id');
            $table->uuid('work_order_operation_id')->nullable();
            $table->uuid('work_center_id')->nullable();
            $table->decimal('quantity_produced', 15, 4);
            $table->decimal('quantity_rejected', 15, 4)->default(0);
            $table->string('rejection_reason', 255)->nullable();
            $table->timestamp('production_date');
            $table->uuid('shift_id')->nullable();
            $table->uuid('operator_id');
            $table->text('notes')->nullable();
            $table->timestamps();

            if ($useForeignKeys) {
                $table->foreign('work_order_id')->references('id')->on('manufacturing.work_orders');
            }
            $table->index(['organization_id', 'work_order_id']);
            $table->index(['organization_id', 'production_date']);
        });

        // Production Plans
        Schema::create('manufacturing.production_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('plan_number', 50);
            $table->date('plan_date');
            $table->date('planning_period_start');
            $table->date('planning_period_end');
            $table->string('status', 20)->default('DRAFT'); // 'DRAFT', 'APPROVED', 'EXECUTING', 'COMPLETED'
            $table->uuid('created_by');
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'plan_number']);
            $table->index(['organization_id', 'planning_period_start', 'planning_period_end']);
        });

        // Production Plan Items
        Schema::create('manufacturing.production_plan_items', function (Blueprint $table) use ($useForeignKeys) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('production_plan_id');
            $table->uuid('item_id');
            $table->decimal('planned_quantity', 15, 4);
            $table->date('scheduled_start_date');
            $table->date('scheduled_end_date')->nullable();
            $table->integer('priority')->default(5);
            $table->string('demand_source', 50)->nullable(); // 'SALES_ORDER', 'FORECAST', 'STOCK_REPLENISHMENT'
            $table->uuid('demand_reference_id')->nullable();
            $table->boolean('work_orders_generated')->default(false);
            $table->timestamps();

            if ($useForeignKeys) {
                $table->foreign('production_plan_id')->references('id')->on('manufacturing.production_plans')->onDelete('cascade');
            }
            $table->unique(['production_plan_id', 'item_id', 'scheduled_start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturing.production_plan_items');
        Schema::dropIfExists('manufacturing.production_plans');
        Schema::dropIfExists('manufacturing.production_logs');
        Schema::dropIfExists('manufacturing.work_order_operations');
        Schema::dropIfExists('manufacturing.work_order_materials');
        Schema::dropIfExists('manufacturing.work_orders');
        Schema::dropIfExists('manufacturing.bom_lines');
        Schema::dropIfExists('manufacturing.bom_headers');
        Schema::dropIfExists('manufacturing.routing_operations');
        Schema::dropIfExists('manufacturing.routings');
        Schema::dropIfExists('manufacturing.work_centers');
    }
};
