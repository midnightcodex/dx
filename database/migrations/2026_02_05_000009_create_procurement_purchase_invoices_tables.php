<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Procurement purchase invoices (non-accounting) tables.
     */
    public function up(): void
    {
        $useForeignKeys = DB::getDriverName() !== 'sqlite';

        Schema::create('procurement.purchase_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('invoice_number', 50)->unique();
            $table->uuid('vendor_id');
            $table->uuid('purchase_order_id')->nullable();
            $table->uuid('grn_id')->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('status', 20)->default('DRAFT');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->uuid('created_by')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'vendor_id']);
        });

        Schema::create('procurement.purchase_invoice_lines', function (Blueprint $table) use ($useForeignKeys) {
            $table->uuid('id')->primary();
            $table->uuid('purchase_invoice_id');
            $table->integer('line_number');
            $table->uuid('item_id');
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('unit_price', 15, 4)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_amount', 15, 2)->default(0);
            $table->timestamps();

            if ($useForeignKeys) {
                $table->foreign('purchase_invoice_id')->references('id')->on('procurement.purchase_invoices')->onDelete('cascade');
            }
            $table->unique(['purchase_invoice_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement.purchase_invoice_lines');
        Schema::dropIfExists('procurement.purchase_invoices');
    }
};
