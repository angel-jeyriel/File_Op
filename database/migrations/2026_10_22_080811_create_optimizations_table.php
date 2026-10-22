<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('optimizations', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 32)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('api_key_id')->constrained()->restrictOnDelete();
            $table->string('idempotency_key', 128)->nullable();
            $table->string('status', 24)->default('pending')->index();
            $table->string('file_category', 16);
            $table->json('options');
            $table->unsignedBigInteger('original_size_bytes')->nullable();
            $table->unsignedBigInteger('output_size_bytes')->nullable();
            $table->unsignedInteger('processing_time_ms')->nullable();
            $table->string('error_code', 64)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->unique(['user_id', 'api_key_id', 'idempotency_key'], 'optimizations_idempotency_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('optimizations');
    }
};
