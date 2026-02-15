<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Auth module tables for users, roles, and permissions.
     * Uses the 'auth' schema for module isolation.
     */
    public function up(): void
    {
        $useForeignKeys = DB::getDriverName() !== 'sqlite';

        // Organizations table (multi-tenancy foundation)
        Schema::create('shared.organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('code', 50)->unique();
            $table->string('tax_id', 50)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('timezone', 50)->default('UTC');
            $table->string('currency', 3)->default('INR');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Users table in auth schema
        Schema::create('auth.users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->string('avatar_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'email']);
            $table->index(['organization_id', 'is_active']);
        });

        // Roles table
        Schema::create('auth.roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false); // System roles can't be deleted
            $table->timestamps();

            $table->unique(['organization_id', 'slug']);
            $table->index('organization_id');
        });

        // Permissions table
        Schema::create('auth.permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('module', 50); // e.g., 'manufacturing', 'inventory'
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('module');
        });

        // Role-Permission pivot
        Schema::create('auth.role_permissions', function (Blueprint $table) use ($useForeignKeys) {
            $table->uuid('id')->primary();
            $table->uuid('role_id');
            $table->uuid('permission_id');
            $table->timestamps();

            if ($useForeignKeys) {
                $table->foreign('role_id')->references('id')->on('auth.roles')->onDelete('cascade');
                $table->foreign('permission_id')->references('id')->on('auth.permissions')->onDelete('cascade');
            }
            $table->unique(['role_id', 'permission_id']);
        });

        // User-Role pivot
        Schema::create('auth.user_roles', function (Blueprint $table) use ($useForeignKeys) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('role_id');
            $table->timestamps();

            if ($useForeignKeys) {
                $table->foreign('user_id')->references('id')->on('auth.users')->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on('auth.roles')->onDelete('cascade');
            }
            $table->unique(['user_id', 'role_id']);
        });

        // Password reset tokens
        Schema::create('auth.password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Sessions table (for database session driver)
        Schema::create('auth.sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth.sessions');
        Schema::dropIfExists('auth.password_reset_tokens');
        Schema::dropIfExists('auth.user_roles');
        Schema::dropIfExists('auth.role_permissions');
        Schema::dropIfExists('auth.permissions');
        Schema::dropIfExists('auth.roles');
        Schema::dropIfExists('auth.users');
        Schema::dropIfExists('shared.organizations');
    }
};
