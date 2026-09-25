<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('version', 20);
            $table->string('author')->nullable();
            $table->string('author_email')->nullable();
            $table->string('homepage')->nullable();
            $table->string('license', 20)->nullable()->default('proprietary');
            $table->json('requires')->nullable(); // dependencies
            $table->json('permissions')->nullable();
            $table->json('settings')->nullable();
            $table->json('config')->nullable();
            $table->enum('status', ['installed', 'active', 'inactive', 'error', 'uninstalled'])->default('installed');
            $table->integer('sort_order')->default(0);
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_health_check_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        // Addon version history
        Schema::create('addon_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('addon_id')->constrained()->cascadeOnDelete();
            $table->string('version', 20);
            $table->text('changelog')->nullable();
            $table->json('migration_files')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->unique(['addon_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addon_versions');
        Schema::dropIfExists('addons');
    }
};