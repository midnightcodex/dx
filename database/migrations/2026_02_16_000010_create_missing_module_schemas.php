<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Ensure all module schemas referenced by migrations exist.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $schemas = ['integrations', 'reports'];

        foreach ($schemas as $schema) {
            DB::statement("CREATE SCHEMA IF NOT EXISTS {$schema}");
        }
    }

    /**
     * No destructive rollback here to avoid accidental table drops via CASCADE.
     */
    public function down(): void
    {
        // Intentionally left blank.
    }
};
