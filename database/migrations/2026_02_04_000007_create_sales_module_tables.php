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
        // 1. Customers Table
        Schema::create('sales.customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('payment_terms')->nullable(); // Net 30, COD, etc.
            $table->string('currency', 3)->default('USD');
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
        });

        // 2. Sales Orders Table
        Schema::create('sales.sales_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('so_number')->unique(); // SO-202602-0001
            $table->uuid('customer_id');
            $table->date('order_date');
            $table->date('expected_ship_date')->nullable();
            $table->string('status', 20)->default('DRAFT'); // DRAFT, CONFIRMED, SHIPPED, CANCELLED

            // Financials
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->text('notes')->nullable();
            $table->text('shipping_address_snapshot')->nullable(); // Snapshot at time of order
            $table->text('billing_address_snapshot')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('sales.customers');
            $table->index(['organization_id', 'status']);
        });

        // 3. Sales Order Lines
        Schema::create('sales.sales_order_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('sales_order_id');
            $table->uuid('item_id'); // From inventory.items

            $table->decimal('quantity', 10, 4);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('line_amount', 15, 2); // (qty * price) + tax

            $table->decimal('shipped_quantity', 10, 4)->default(0); // Track partial shipments

            $table->timestamps();

            $table->foreign('sales_order_id')->references('id')->on('sales.sales_orders')->onDelete('cascade');
        });

        // 4. Shipments (Fulfillment)
        Schema::create('sales.shipments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('shipment_number')->unique(); // SH-202602-0001
            $table->uuid('sales_order_id');
            $table->uuid('warehouse_id'); // inventory.warehouses
            $table->date('shipment_date');
            $table->string('status', 20)->default('DRAFT'); // DRAFT, SHIPPED, DELIVERED, CANCELLED
            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('notes')->nullable();

            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('sales_order_id')->references('id')->on('sales.sales_orders');
        });

        // 5. Shipment Lines
        Schema::create('sales.shipment_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('shipment_id');
            $table->uuid('sales_order_line_id');
            $table->uuid('item_id');
            $table->decimal('quantity', 10, 4);

            $table->timestamps();

            $table->foreign('shipment_id')->references('id')->on('sales.shipments')->onDelete('cascade');
            $table->foreign('sales_order_line_id')->references('id')->on('sales.sales_order_lines');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales.shipment_lines');
        Schema::dropIfExists('sales.shipments');
        Schema::dropIfExists('sales.sales_order_lines');
        Schema::dropIfExists('sales.sales_orders');
        Schema::dropIfExists('sales.customers');
    }
};
