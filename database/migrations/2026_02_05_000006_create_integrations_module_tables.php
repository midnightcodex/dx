<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Integrations module tables - accounting exports, barcode, weighbridge, API configs.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::create('integrations.accounting_exports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('export_number', 50)->unique();
            $table->date('export_date')->nullable();
            $table->string('export_type', 50)->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->jsonb('reference_ids')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->string('file_format', 20)->nullable();
            $table->string('status', 20)->nullable();
            $table->uuid('exported_by')->nullable();
            $table->timestamp('exported_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('integrations.barcode_labels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('label_type', 50)->nullable();
            $table->string('entity_type', 50)->nullable();
            $table->uuid('entity_id')->nullable();
            $table->string('barcode', 255)->unique();
            $table->string('barcode_format', 20)->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'entity_type', 'entity_id']);
            $table->index(['barcode']);
        });

        Schema::create('integrations.weighbridge_readings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('reading_number', 50)->unique();
            $table->timestamp('reading_date')->nullable();
            $table->string('vehicle_number', 50)->nullable();
            $table->decimal('tare_weight', 15, 3)->nullable();
            $table->decimal('gross_weight', 15, 3)->nullable();
            $table->decimal('net_weight', 15, 3)->nullable();
            $table->string('uom', 10)->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->uuid('weighbridge_operator')->nullable();
            $table->timestamps();
        });

        Schema::create('integrations.api_configurations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('integration_name', 100)->nullable();
            $table->string('api_endpoint', 500)->nullable();
            $table->string('auth_type', 50)->nullable();
            $table->jsonb('credentials')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->integer('sync_frequency_minutes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::dropIfExists('integrations.api_configurations');
        Schema::dropIfExists('integrations.weighbridge_readings');
        Schema::dropIfExists('integrations.barcode_labels');
        Schema::dropIfExists('integrations.accounting_exports');
    }
};
