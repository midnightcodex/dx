<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Sales return tables - returns and return lines.
     */
    public function up(): void
    {
        $useForeignKeys = DB::getDriverName() !== 'sqlite';

        Schema::create('sales.sales_returns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('return_number', 50)->unique();
            $table->uuid('customer_id');
            $table->uuid('sales_order_id')->nullable();
            $table->uuid('delivery_note_id')->nullable();
            $table->date('return_date')->nullable();
            $table->string('status', 20)->default('DRAFT');
            $table->text('reason')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sales.sales_return_lines', function (Blueprint $table) use ($useForeignKeys) {
            $table->uuid('id')->primary();
            $table->uuid('sales_return_id');
            $table->integer('line_number');
            $table->uuid('delivery_note_line_id')->nullable();
            $table->uuid('item_id');
            $table->decimal('returned_quantity', 15, 4)->default(0);
            $table->decimal('accepted_quantity', 15, 4)->default(0);
            $table->decimal('rejected_quantity', 15, 4)->default(0);
            $table->uuid('batch_id')->nullable();
            $table->string('disposition', 50)->nullable();
            $table->timestamps();

            if ($useForeignKeys) {
                $table->foreign('sales_return_id')->references('id')->on('sales.sales_returns')->onDelete('cascade');
            }
            $table->unique(['sales_return_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales.sales_return_lines');
        Schema::dropIfExists('sales.sales_returns');
    }
};
