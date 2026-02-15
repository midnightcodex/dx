<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $useForeignKeys = DB::getDriverName() !== 'sqlite';

        Schema::create('shared.approval_workflows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('workflow_name', 255);
            $table->string('document_type', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'document_type']);
        });

        Schema::create('shared.approval_workflow_steps', function (Blueprint $table) use ($useForeignKeys) {
            $table->uuid('id')->primary();
            $table->uuid('workflow_id');
            $table->integer('step_number');
            $table->uuid('role_id')->nullable();
            $table->decimal('min_amount', 15, 2)->nullable();
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->timestamps();

            if ($useForeignKeys) {
                $table->foreign('workflow_id')->references('id')->on('shared.approval_workflows')->onDelete('cascade');
            }
            $table->unique(['workflow_id', 'step_number']);
        });

        Schema::table('shared.approval_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('shared.approval_requests', 'reference_type')) {
                $table->string('reference_type', 100)->nullable();
            }
            if (!Schema::hasColumn('shared.approval_requests', 'reference_id')) {
                $table->uuid('reference_id')->nullable();
            }
            if (!Schema::hasColumn('shared.approval_requests', 'workflow_id')) {
                $table->uuid('workflow_id')->nullable();
            }
            if (!Schema::hasColumn('shared.approval_requests', 'requested_at')) {
                $table->timestamp('requested_at')->nullable();
            }
            if (!Schema::hasColumn('shared.approval_requests', 'rejected_by')) {
                $table->uuid('rejected_by')->nullable();
            }
            if (!Schema::hasColumn('shared.approval_requests', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable();
            }
            if (!Schema::hasColumn('shared.approval_requests', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('shared.approval_requests', function (Blueprint $table) {
            $columns = [];
            foreach ([
                'rejection_reason',
                'rejected_at',
                'rejected_by',
                'requested_at',
                'workflow_id',
                'reference_id',
                'reference_type',
            ] as $column) {
                if (Schema::hasColumn('shared.approval_requests', $column)) {
                    $columns[] = $column;
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });

        Schema::dropIfExists('shared.approval_workflow_steps');
        Schema::dropIfExists('shared.approval_workflows');
    }
};
