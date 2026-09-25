<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });

        // Push notification subscriptions
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('endpoint');
            $table->text('public_key')->nullable();
            $table->text('auth_token')->nullable();
            $table->text('content_encoding')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'endpoint']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('notifications');
    }
};