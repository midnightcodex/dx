<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Procurement module tables - Vendors, Purchase Orders, GRN.
     */
    public function up(): void
    {
        $useForeignKeys = DB::getDriverName() !== 'sqlite';

        // Vendors / Suppliers
        Schema::create('procurement.vendors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('vendor_code', 50);
            $table->string('name', 255);
            $table->string('contact_person', 100)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('tax_id', 50)->nullable();
            $table->string('payment_terms', 50)->nullable(); // 'NET30', 'NET60', 'IMMEDIATE'
            $table->string('currency', 3)->default('INR');
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->string('status', 20)->default('ACTIVE'); // 'ACTIVE', 'INACTIVE', 'BLACKLISTED'
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'vendor_code']);
            $table->index(['organization_id', 'status']);
        });

        // Purchase Orders (Header)
        Schema::create('procurement.purchase_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('po_number', 50);
            $table->uuid('vendor_id');
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->uuid('delivery_warehouse_id');
            $table->string('status', 20)->default('DRAFT'); // 'DRAFT', 'SUBMITTED', 'APPROVED', 'PARTIAL', 'COMPLETED', 'CANCELLED'
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->decimal('exchange_rate', 15, 6)->default(1);
            $table->string('payment_terms', 50)->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by');
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'po_number']);
            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'vendor_id']);
        });

        // Purchase Order Lines
        Schema::create('procurement.purchase_order_lines', function (Blueprint $table) use ($useForeignKeys) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('purchase_order_id');
            $table->integer('line_number');
            $table->uuid('item_id');
            $table->text('description')->nullable();
            $table->decimal('quantity', 15, 4);
            $table->uuid('uom_id')->nullable();
            $table->decimal('unit_price', 15, 4);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('line_amount', 15, 2);
            $table->decimal('received_quantity', 15, 4)->default(0);
            $table->date('expected_date')->nullable();
            $table->timestamps();

            if ($useForeignKeys) {
                $table->foreign('purchase_order_id')->references('id')->on('procurement.purchase_orders')->onDelete('cascade');
            }
            $table->unique(['purchase_order_id', 'line_number']);
            $table->index(['organization_id', 'item_id']);
        });

        // Goods Receipt Notes (GRN) - Header
        Schema::create('procurement.goods_receipt_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('grn_number', 50);
            $table->uuid('purchase_order_id');
            $table->uuid('vendor_id');
            $table->uuid('warehouse_id');
            $table->date('receipt_date');
            $table->string('status', 20)->default('DRAFT'); // 'DRAFT', 'INSPECTING', 'APPROVED', 'POSTED', 'CANCELLED'
            $table->string('supplier_invoice_number', 100)->nullable();
            $table->date('supplier_invoice_date')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('received_by');
            $table->uuid('posted_by')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'grn_number']);
            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'purchase_order_id']);
        });

        // GRN Lines
        Schema::create('procurement.grn_lines', function (Blueprint $table) use ($useForeignKeys) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('grn_id');
            $table->uuid('po_line_id');
            $table->uuid('item_id');
            $table->decimal('ordered_quantity', 15, 4);
            $table->decimal('received_quantity', 15, 4);
            $table->decimal('accepted_quantity', 15, 4);
            $table->decimal('rejected_quantity', 15, 4)->default(0);
            $table->string('rejection_reason', 255)->nullable();
            $table->uuid('batch_id')->nullable();
            $table->string('batch_number', 100)->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('unit_price', 15, 4);
            $table->string('quality_status', 20)->default('PENDING'); // 'PENDING', 'PASSED', 'FAILED', 'PARTIAL'
            $table->timestamps();

            if ($useForeignKeys) {
                $table->foreign('grn_id')->references('id')->on('procurement.goods_receipt_notes')->onDelete('cascade');
            }
            $table->index(['organization_id', 'item_id']);
        });

        // Purchase Requisitions (optional - for approval workflow)
        Schema::create('procurement.purchase_requisitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('requisition_number', 50);
            $table->uuid('requester_id');
            $table->date('required_date');
            $table->string('status', 20)->default('DRAFT'); // 'DRAFT', 'SUBMITTED', 'APPROVED', 'CONVERTED', 'CANCELLED'
            $table->string('priority', 20)->default('NORMAL'); // 'LOW', 'NORMAL', 'HIGH', 'URGENT'
            $table->text('reason')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->uuid('converted_po_id')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'requisition_number']);
            $table->index(['organization_id', 'status']);
        });

        // Purchase Requisition Lines
        Schema::create('procurement.purchase_requisition_lines', function (Blueprint $table) use ($useForeignKeys) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('requisition_id');
            $table->uuid('item_id');
            $table->decimal('quantity', 15, 4);
            $table->uuid('uom_id')->nullable();
            $table->uuid('preferred_vendor_id')->nullable();
            $table->decimal('estimated_price', 15, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            if ($useForeignKeys) {
                $table->foreign('requisition_id')->references('id')->on('procurement.purchase_requisitions')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement.purchase_requisition_lines');
        Schema::dropIfExists('procurement.purchase_requisitions');
        Schema::dropIfExists('procurement.grn_lines');
        Schema::dropIfExists('procurement.goods_receipt_notes');
        Schema::dropIfExists('procurement.purchase_order_lines');
        Schema::dropIfExists('procurement.purchase_orders');
        Schema::dropIfExists('procurement.vendors');
    }
};
