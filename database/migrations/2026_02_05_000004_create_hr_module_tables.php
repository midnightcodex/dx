<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * HR module tables - employees, shifts, attendance.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::create('hr.employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('employee_code', 50)->unique();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('full_name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('address_line1', 255)->nullable();
            $table->string('address_line2', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->date('date_of_joining')->nullable();
            $table->date('date_of_leaving')->nullable();
            $table->string('department', 100)->nullable();
            $table->string('designation', 100)->nullable();
            $table->string('employment_type', 50)->nullable();
            $table->uuid('reporting_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
            $table->index(['organization_id', 'employee_code']);
        });

        Schema::create('hr.shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('shift_code', 50)->unique();
            $table->string('shift_name', 100)->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('break_duration_minutes')->default(0);
            $table->boolean('is_night_shift')->default(false);
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('hr.employee_shift_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('employee_id');
            $table->uuid('shift_id');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('hr.employees')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('hr.shifts')->onDelete('cascade');
            $table->unique(['employee_id', 'effective_from']);
        });

        Schema::create('hr.attendance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('employee_id');
            $table->date('attendance_date');
            $table->uuid('shift_id')->nullable();
            $table->timestamp('clock_in_time')->nullable();
            $table->timestamp('clock_out_time')->nullable();
            $table->integer('work_duration_minutes')->nullable();
            $table->string('status', 20)->nullable();
            $table->integer('late_arrival_minutes')->default(0);
            $table->integer('early_departure_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('hr.employees')->onDelete('cascade');
            $table->unique(['organization_id', 'employee_id', 'attendance_date']);
            $table->index(['organization_id', 'employee_id', 'attendance_date']);
            $table->index(['organization_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::dropIfExists('hr.attendance');
        Schema::dropIfExists('hr.employee_shift_assignments');
        Schema::dropIfExists('hr.shifts');
        Schema::dropIfExists('hr.employees');
    }
};
