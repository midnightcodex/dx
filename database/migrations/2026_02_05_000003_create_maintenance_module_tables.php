<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Maintenance module tables - machines, preventive maintenance, breakdowns.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::create('maintenance.machines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('machine_code', 50)->unique();
            $table->string('machine_name', 255);
            $table->string('machine_type', 100)->nullable();
            $table->string('manufacturer', 255)->nullable();
            $table->string('model_number', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('installation_date')->nullable();
            $table->date('warranty_expiry_date')->nullable();
            $table->string('location', 255)->nullable();
            $table->uuid('work_center_id')->nullable();
            $table->string('capacity', 100)->nullable();
            $table->string('power_rating', 100)->nullable();
            $table->integer('maintenance_frequency_days')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->string('status', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
            $table->index(['organization_id', 'status']);
        });

        Schema::create('maintenance.machine_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('machine_id');
            $table->string('document_type', 50)->nullable();
            $table->string('document_name', 255)->nullable();
            $table->string('document_path', 500)->nullable();
            $table->timestamp('uploaded_at')->nullable();

            $table->foreign('machine_id')->references('id')->on('maintenance.machines')->onDelete('cascade');
        });

        Schema::create('maintenance.preventive_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('schedule_code', 50)->nullable()->unique();
            $table->uuid('machine_id');
            $table->string('frequency_type', 20)->nullable();
            $table->integer('frequency_value')->nullable();
            $table->uuid('checklist_template_id')->nullable();
            $table->date('last_performed_date')->nullable();
            $table->date('next_due_date')->nullable();
            $table->uuid('assigned_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('machine_id')->references('id')->on('maintenance.machines')->onDelete('cascade');
            $table->index(['organization_id', 'machine_id']);
        });

        Schema::create('maintenance.preventive_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('task_number', 50)->nullable()->unique();
            $table->uuid('schedule_id')->nullable();
            $table->uuid('machine_id')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->string('status', 20)->nullable();
            $table->uuid('assigned_to')->nullable();
            $table->uuid('performed_by')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->text('findings')->nullable();
            $table->text('actions_taken')->nullable();
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('maintenance.preventive_schedules')->onDelete('set null');
            $table->index(['organization_id', 'status']);
        });

        Schema::create('maintenance.maintenance_checklist_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id');
            $table->integer('item_number');
            $table->string('check_description', 500)->nullable();
            $table->string('check_type', 20)->nullable();
            $table->string('expected_value', 100)->nullable();
            $table->string('actual_value', 100)->nullable();
            $table->boolean('is_ok')->nullable();
            $table->text('remarks')->nullable();

            $table->foreign('task_id')->references('id')->on('maintenance.preventive_tasks')->onDelete('cascade');
            $table->unique(['task_id', 'item_number']);
        });

        Schema::create('maintenance.breakdown_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('ticket_number', 50)->unique();
            $table->uuid('machine_id');
            $table->timestamp('reported_at')->nullable();
            $table->uuid('reported_by')->nullable();
            $table->text('problem_description')->nullable();
            $table->string('severity', 20)->nullable();
            $table->string('status', 20)->nullable();
            $table->uuid('assigned_to')->nullable();
            $table->timestamp('work_started_at')->nullable();
            $table->timestamp('work_completed_at')->nullable();
            $table->integer('downtime_minutes')->nullable();
            $table->decimal('production_loss_estimate', 15, 2)->nullable();
            $table->text('root_cause')->nullable();
            $table->text('corrective_action')->nullable();
            $table->text('preventive_action')->nullable();
            $table->text('spare_parts_used')->nullable();
            $table->decimal('labor_cost', 15, 2)->nullable();
            $table->decimal('parts_cost', 15, 2)->nullable();
            $table->decimal('total_cost', 15, 2)->nullable();
            $table->uuid('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('machine_id')->references('id')->on('maintenance.machines')->onDelete('cascade');
            $table->index(['organization_id', 'machine_id']);
            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'severity', 'status']);
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::dropIfExists('maintenance.breakdown_reports');
        Schema::dropIfExists('maintenance.maintenance_checklist_items');
        Schema::dropIfExists('maintenance.preventive_tasks');
        Schema::dropIfExists('maintenance.preventive_schedules');
        Schema::dropIfExists('maintenance.machine_documents');
        Schema::dropIfExists('maintenance.machines');
    }
};
