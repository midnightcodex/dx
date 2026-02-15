<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $useForeignKeys = DB::getDriverName() !== 'sqlite';

        Schema::table('sales.sales_order_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('sales.sales_order_lines', 'line_number')) {
                $table->integer('line_number')->default(1);
            }
            if (!Schema::hasColumn('sales.sales_order_lines', 'uom_id')) {
                $table->uuid('uom_id')->nullable();
            }
            if (!Schema::hasColumn('sales.sales_order_lines', 'reserved_quantity')) {
                $table->decimal('reserved_quantity', 15, 4)->default(0);
            }
            if (!Schema::hasColumn('sales.sales_order_lines', 'dispatched_quantity')) {
                $table->decimal('dispatched_quantity', 15, 4)->default(0);
            }
            if (!Schema::hasColumn('sales.sales_order_lines', 'reserved_warehouse_id')) {
                $table->uuid('reserved_warehouse_id')->nullable();
            }
            if (!Schema::hasColumn('sales.sales_order_lines', 'reserved_batch_id')) {
                $table->uuid('reserved_batch_id')->nullable();
            }
        });

        Schema::table('sales.delivery_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('sales.delivery_notes', 'dispatched_at')) {
                $table->timestamp('dispatched_at')->nullable();
            }
            if (!Schema::hasColumn('sales.delivery_notes', 'dispatched_by')) {
                $table->uuid('dispatched_by')->nullable();
            }
            if (!Schema::hasColumn('sales.delivery_notes', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable();
            }
        });

        Schema::create('sales.delivery_note_lines', function (Blueprint $table) use ($useForeignKeys) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('delivery_note_id');
            $table->integer('line_number');
            $table->uuid('sales_order_line_id')->nullable();
            $table->uuid('item_id');
            $table->decimal('quantity', 15, 4);
            $table->uuid('uom_id')->nullable();
            $table->uuid('batch_id')->nullable();
            $table->timestamps();

            if ($useForeignKeys) {
                $table->foreign('delivery_note_id')->references('id')->on('sales.delivery_notes')->onDelete('cascade');
            }
            $table->unique(['delivery_note_id', 'line_number']);
            $table->index(['organization_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales.delivery_note_lines');

        Schema::table('sales.delivery_notes', function (Blueprint $table) {
            if (Schema::hasColumn('sales.delivery_notes', 'delivered_at')) {
                $table->dropColumn('delivered_at');
            }
            if (Schema::hasColumn('sales.delivery_notes', 'dispatched_by')) {
                $table->dropColumn('dispatched_by');
            }
            if (Schema::hasColumn('sales.delivery_notes', 'dispatched_at')) {
                $table->dropColumn('dispatched_at');
            }
        });

        Schema::table('sales.sales_order_lines', function (Blueprint $table) {
            $columns = [];
            foreach ([
                'reserved_batch_id',
                'reserved_warehouse_id',
                'dispatched_quantity',
                'reserved_quantity',
                'uom_id',
                'line_number',
            ] as $column) {
                if (Schema::hasColumn('sales.sales_order_lines', $column)) {
                    $columns[] = $column;
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
