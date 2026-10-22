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
        Schema::create('usage_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('api_key_id')->constrained()->restrictOnDelete();
            $table->foreignId('optimization_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('input_bytes');
            $table->unsignedBigInteger('output_bytes');
            $table->unsignedInteger('processing_time_ms');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['api_key_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_records');
    }
};
