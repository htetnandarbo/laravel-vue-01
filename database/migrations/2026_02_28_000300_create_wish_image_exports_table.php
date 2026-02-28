<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wish_image_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_id')->constrained('qrs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('queued')->index();
            $table->string('file_path')->nullable();
            $table->unsignedInteger('total_images')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['qr_id', 'user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wish_image_exports');
    }
};
