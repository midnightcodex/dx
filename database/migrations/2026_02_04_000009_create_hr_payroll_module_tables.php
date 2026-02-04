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
        // 1. Departments
        Schema::create('hr.departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->string('name');
            $table->uuid('manager_id')->nullable(); // Employee ID
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Employees
        Schema::create('hr.employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('user_id')->nullable()->index(); // Link to System User
            $table->uuid('department_id')->nullable();
            $table->string('employee_code')->unique(); // EMP-001
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('designation')->nullable();
            $table->date('date_of_joining')->nullable();
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE, RESIGNED, TERMINATED

            // Bank Details
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('tax_id')->nullable(); // PAN/SSN

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('department_id')->references('id')->on('hr.departments');
        });

        // 3. Leave Types
        Schema::create('hr.leave_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->string('name'); // Sick, Casual, Earned
            $table->integer('default_days_per_year')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Leave Allocations
        Schema::create('hr.leave_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('employee_id');
            $table->uuid('leave_type_id');
            $table->decimal('days_allocated', 8, 2)->default(0);
            $table->decimal('days_used', 8, 2)->default(0);
            $table->integer('year');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('hr.employees');
            $table->foreign('leave_type_id')->references('id')->on('hr.leave_types');
            $table->unique(['employee_id', 'leave_type_id', 'year']);
        });

        // 5. Leave Requests
        Schema::create('hr.leave_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('employee_id');
            $table->uuid('leave_type_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days_requested', 5, 2); // e.g., 0.5 or 1 or 2
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('PENDING'); // PENDING, APPROVED, REJECTED
            $table->uuid('approver_id')->nullable(); // User ID (Manager)
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')->references('id')->on('hr.employees');
            $table->foreign('leave_type_id')->references('id')->on('hr.leave_types');
        });

        // 6. Salary Components
        Schema::create('hr.salary_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->string('name'); // Basic, HRA, PF
            $table->string('type', 20); // EARNING, DEDUCTION
            $table->boolean('is_fixed')->default(true); // Fixed monthly or variable?
            $table->timestamps();
        });

        // 7. Employee Salary Structure
        Schema::create('hr.employee_salary_structures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('employee_id');
            $table->uuid('salary_component_id');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('hr.employees');
            $table->foreign('salary_component_id')->references('id')->on('hr.salary_components');
        });

        // 8. Payrolls (Run Header)
        Schema::create('hr.payrolls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->integer('month');
            $table->integer('year');
            $table->string('status', 20)->default('DRAFT'); // DRAFT, PROCESSED, PAID
            $table->timestamp('processed_at')->nullable();
            $table->uuid('processed_by')->nullable();
            $table->timestamps();
        });

        // 9. Payslips
        Schema::create('hr.payslips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('payroll_id');
            $table->uuid('employee_id');
            $table->decimal('gross_earnings', 15, 2);
            $table->decimal('total_deductions', 15, 2);
            $table->decimal('net_pay', 15, 2);
            $table->string('status', 20)->default('DRAFT'); // DRAFT, GENERATED
            $table->timestamps();

            $table->foreign('payroll_id')->references('id')->on('hr.payrolls');
            $table->foreign('employee_id')->references('id')->on('hr.employees');
        });

        // 10. Payslip Items
        Schema::create('hr.payslip_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('payslip_id');
            $table->string('component_name');
            $table->string('type', 20); // EARNING, DEDUCTION
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->foreign('payslip_id')->references('id')->on('hr.payslips');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr.payslip_items');
        Schema::dropIfExists('hr.payslips');
        Schema::dropIfExists('hr.payrolls');
        Schema::dropIfExists('hr.employee_salary_structures');
        Schema::dropIfExists('hr.salary_components');
        Schema::dropIfExists('hr.leave_requests');
        Schema::dropIfExists('hr.leave_allocations');
        Schema::dropIfExists('hr.leave_types');
        Schema::dropIfExists('hr.employees');
        Schema::dropIfExists('hr.departments');
    }
};
