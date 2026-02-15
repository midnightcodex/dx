<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('manufacturing.cost_centers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('code', 50);
            $table->string('name', 255);
            $table->string('category', 50)->default('DIRECT');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['organization_id', 'code']);
        });

        Schema::create('manufacturing.work_order_costs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('work_order_id');
            $table->string('cost_type', 50);
            $table->uuid('cost_center_id')->nullable();
            $table->decimal('standard_cost', 15, 4)->default(0);
            $table->decimal('actual_cost', 15, 4)->default(0);
            $table->decimal('variance', 15, 4)->default(0);
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('rate', 15, 4)->default(0);
            $table->timestamp('calculation_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['work_order_id', 'cost_type', 'cost_center_id'], 'wo_costs_unique');
            $table->index(['organization_id', 'work_order_id']);
        });

        Schema::create('manufacturing.quality_parameters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('code', 50);
            $table->string('name', 255);
            $table->string('parameter_type', 50);
            $table->uuid('uom_id')->nullable();
            $table->decimal('min_value', 15, 4)->nullable();
            $table->decimal('max_value', 15, 4)->nullable();
            $table->decimal('target_value', 15, 4)->nullable();
            $table->decimal('tolerance', 15, 4)->nullable();
            $table->boolean('is_critical')->default(false);
            $table->timestamps();

            $table->unique(['organization_id', 'code']);
        });

        Schema::create('manufacturing.quality_inspection_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('template_code', 50);
            $table->string('name', 255);
            $table->string('inspection_type', 50)->nullable();
            $table->string('applicable_to', 50)->nullable();
            $table->string('frequency', 50)->nullable();
            $table->integer('sampling_size')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'template_code']);
        });

        Schema::create('manufacturing.quality_template_parameters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id');
            $table->uuid('parameter_id');
            $table->boolean('is_mandatory')->default(true);
            $table->integer('sequence')->default(1);
            $table->timestamps();

            $table->unique(['template_id', 'parameter_id']);
        });

        Schema::create('manufacturing.quality_inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('inspection_number', 50);
            $table->uuid('template_id')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->uuid('item_id')->nullable();
            $table->uuid('batch_id')->nullable();
            $table->decimal('quantity_inspected', 15, 4)->default(0);
            $table->timestamp('inspection_date')->nullable();
            $table->uuid('inspected_by')->nullable();
            $table->string('status', 20)->default('PENDING');
            $table->string('overall_result', 20)->nullable();
            $table->text('remarks')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'inspection_number']);
            $table->index(['organization_id', 'reference_type', 'reference_id']);
        });

        Schema::create('manufacturing.quality_inspection_readings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inspection_id');
            $table->uuid('parameter_id');
            $table->string('reading_value', 255)->nullable();
            $table->decimal('numeric_value', 15, 4)->nullable();
            $table->boolean('is_within_spec')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('manufacturing.ncr_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('ncr_number', 50);
            $table->uuid('inspection_id')->nullable();
            $table->text('defect_description')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('corrective_action')->nullable();
            $table->text('preventive_action')->nullable();
            $table->uuid('raised_by')->nullable();
            $table->uuid('assigned_to')->nullable();
            $table->string('status', 20)->default('OPEN');
            $table->timestamp('raised_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'ncr_number']);
        });

        Schema::create('manufacturing.scrap_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('scrap_number', 50);
            $table->string('source_type', 50)->nullable();
            $table->uuid('source_id')->nullable();
            $table->uuid('item_id');
            $table->decimal('scrap_quantity', 15, 4)->default(0);
            $table->decimal('scrap_value', 15, 4)->default(0);
            $table->string('scrap_reason', 255)->nullable();
            $table->string('scrap_category', 50)->nullable();
            $table->uuid('warehouse_id')->nullable();
            $table->uuid('batch_id')->nullable();
            $table->string('disposal_method', 50)->nullable();
            $table->decimal('disposed_quantity', 15, 4)->default(0);
            $table->date('disposal_date')->nullable();
            $table->uuid('recorded_by')->nullable();
            $table->uuid('inventory_transaction_id')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'scrap_number']);
            $table->index(['organization_id', 'item_id']);
        });

        Schema::create('manufacturing.scrap_recovery', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('scrap_entry_id');
            $table->uuid('recovered_item_id')->nullable();
            $table->decimal('recovered_quantity', 15, 4)->default(0);
            $table->decimal('recovery_value', 15, 4)->default(0);
            $table->date('recovery_date')->nullable();
            $table->string('sold_to', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturing.scrap_recovery');
        Schema::dropIfExists('manufacturing.scrap_entries');
        Schema::dropIfExists('manufacturing.ncr_reports');
        Schema::dropIfExists('manufacturing.quality_inspection_readings');
        Schema::dropIfExists('manufacturing.quality_inspections');
        Schema::dropIfExists('manufacturing.quality_template_parameters');
        Schema::dropIfExists('manufacturing.quality_inspection_templates');
        Schema::dropIfExists('manufacturing.quality_parameters');
        Schema::dropIfExists('manufacturing.work_order_costs');
        Schema::dropIfExists('manufacturing.cost_centers');
    }
};
