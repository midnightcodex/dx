<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Compliance module tables - documents, audit logs, certifications.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::create('compliance.documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('document_number', 50)->unique();
            $table->string('document_name', 255)->nullable();
            $table->string('document_type', 50)->nullable();
            $table->string('version', 20)->nullable();
            $table->integer('revision_number')->default(1);
            $table->string('department', 100)->nullable();
            $table->date('effective_date')->nullable();
            $table->date('review_date')->nullable();
            $table->date('next_review_date')->nullable();
            $table->string('status', 20)->nullable();
            $table->string('file_path', 500)->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('compliance.document_revisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('document_id');
            $table->integer('revision_number');
            $table->text('changes_description')->nullable();
            $table->uuid('revised_by')->nullable();
            $table->timestamp('revised_at')->nullable();
            $table->string('file_path', 500)->nullable();

            $table->foreign('document_id')->references('id')->on('compliance.documents')->onDelete('cascade');
        });

        Schema::create('compliance.audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('entity_type', 100)->nullable();
            $table->uuid('entity_id')->nullable();
            $table->string('action', 50)->nullable();
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            $table->uuid('changed_by')->nullable();
            $table->timestamp('changed_at')->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->text('user_agent')->nullable();

            $table->index(['organization_id', 'entity_type', 'entity_id']);
            $table->index(['organization_id', 'changed_by', 'changed_at']);
        });

        Schema::create('compliance.certifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('certification_type', 100)->nullable();
            $table->string('certification_number', 100)->nullable();
            $table->string('issuing_authority', 255)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('scope')->nullable();
            $table->string('certificate_file_path', 500)->nullable();
            $table->string('status', 20)->nullable();
            $table->date('next_audit_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::dropIfExists('compliance.certifications');
        Schema::dropIfExists('compliance.audit_logs');
        Schema::dropIfExists('compliance.document_revisions');
        Schema::dropIfExists('compliance.documents');
    }
};
