<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Create PostgreSQL schemas for modular ERP architecture.
     * Each module gets its own schema for logical separation.
     */
    public function up(): void
    {
        // Create schemas for each ERP module
        $schemas = [
            'auth',           // Authentication & Authorization
            'shared',         // Common entities (UOM, Categories, etc.)
            'inventory',      // Inventory management
            'manufacturing',  // Production & BOM
            'procurement',    // Purchasing & Vendors
            'sales',          // Sales Orders & Customers
            'maintenance',    // Equipment & Maintenance
            'hr',             // Human Resources
            'compliance',     // Audit logs & Document control
        ];

        foreach ($schemas as $schema) {
            DB::statement("CREATE SCHEMA IF NOT EXISTS {$schema}");
        }
    }

    /**
     * Drop all ERP schemas (use with caution).
     */
    public function down(): void
    {
        $schemas = [
            'compliance',
            'hr',
            'maintenance',
            'sales',
            'procurement',
            'manufacturing',
            'inventory',
            'shared',
            'auth',
        ];

        foreach ($schemas as $schema) {
            DB::statement("DROP SCHEMA IF EXISTS {$schema} CASCADE");
        }
    }
};
