<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->index();
            $table->text('description')->nullable();
            $table->json('credentials')->nullable();
            $table->json('config')->nullable();
            $table->enum('status', ['active', 'inactive', 'error'])->default('inactive')->index();
            $table->integer('priority')->default(0);
            $table->integer('timeout')->default(30);
            $table->integer('max_retries')->default(3);
            $table->boolean('health_check_enabled')->default(true);
            $table->enum('health_status', ['healthy', 'unhealthy', 'unknown'])->default('unknown');
            $table->timestamp('last_health_check_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index('priority');
        });

        // Provider health log
        Schema::create('provider_health_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['healthy', 'unhealthy']);
            $table->integer('response_time_ms')->nullable();
            $table->text('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('checked_at')->useCurrent();

            $table->index(['provider_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_health_logs');
        Schema::dropIfExists('providers');
    }
};