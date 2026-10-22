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
        Schema::create('stored_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('optimization_id')->constrained()->cascadeOnDelete();
            $table->string('role', 16);
            $table->string('disk', 64);
            $table->string('path', 1024);
            $table->string('original_filename', 255)->nullable();
            $table->string('mime_type', 127);
            $table->string('extension', 16);
            $table->unsignedBigInteger('size_bytes');
            $table->json('metadata')->nullable();
            $table->char('checksum_sha256', 64)->nullable();
            $table->timestamps();

            $table->index(['optimization_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stored_files');
    }
};
