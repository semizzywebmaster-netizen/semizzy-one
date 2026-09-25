<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('slug');
        });

        // Permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('module')->index();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['module', 'slug']);
        });

        // Role-User pivot table
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'user_id']);
            $table->index('user_id');
        });

        // Permission-Role pivot table
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['permission_id', 'role_id']);
            $table->index('role_id');
        });

        // Permission overrides (per-user permission overrides)
        Schema::create('permission_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->boolean('granted')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_overrides');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};