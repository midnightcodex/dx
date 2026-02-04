<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Equipment Table
        Schema::create('maintenance.equipment', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('code')->unique(); // EQ-001
            $table->string('name');
            $table->string('status', 20)->default('OPERATIONAL'); // OPERATIONAL, DOWN, MAINTENANCE
            $table->string('location')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('model_number')->nullable();
            $table->string('manufacturer')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
            $table->index('status');
        });

        // 2. Maintenance Tickets Table
        Schema::create('maintenance.maintenance_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('ticket_number')->unique(); // MT-202602-001
            $table->uuid('equipment_id');
            $table->uuid('reported_by'); // User ID
            $table->uuid('assigned_to')->nullable(); // User ID
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('priority', 20)->default('NORMAL'); // LOW, NORMAL, HIGH, CRITICAL
            $table->string('status', 20)->default('OPEN'); // OPEN, IN_PROGRESS, RESOLVED, CLOSED
            $table->dateTime('completed_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('equipment_id')->references('id')->on('maintenance.equipment');
            $table->index(['organization_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance.maintenance_tickets');
        Schema::dropIfExists('maintenance.equipment');
    }
};
