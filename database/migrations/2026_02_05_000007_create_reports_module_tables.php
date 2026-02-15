<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Reports module tables - report definitions.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::create('reports.report_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->string('report_code', 50)->unique();
            $table->string('report_name', 255)->nullable();
            $table->string('report_category', 50)->nullable();
            $table->text('sql_query')->nullable();
            $table->jsonb('parameters')->nullable();
            $table->boolean('is_system_report')->default(false);
            $table->uuid('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::dropIfExists('reports.report_definitions');
    }
};
