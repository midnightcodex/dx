<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales.customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('customer_code', 50);
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('tax_id', 50)->nullable();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'customer_code']);
            $table->index(['organization_id', 'is_active']);
        });

        Schema::create('sales.sales_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('so_number', 50);
            $table->uuid('customer_id');
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->string('status', 20)->default('DRAFT');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('currency', 10)->default('INR');
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'so_number']);
            $table->index(['organization_id', 'status']);
        });

        Schema::create('sales.sales_order_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('sales_order_id');
            $table->uuid('item_id');
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['organization_id', 'sales_order_id']);
        });

        Schema::create('sales.delivery_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('dn_number', 50);
            $table->uuid('sales_order_id');
            $table->uuid('warehouse_id');
            $table->date('delivery_date');
            $table->string('status', 20)->default('DRAFT');
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'dn_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales.delivery_notes');
        Schema::dropIfExists('sales.sales_order_lines');
        Schema::dropIfExists('sales.sales_orders');
        Schema::dropIfExists('sales.customers');
    }
};
