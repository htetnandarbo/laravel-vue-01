<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_id')->constrained('qrs')->cascadeOnDelete();
            $table->text('message');
            $table->enum('status', ['new', 'seen', 'done'])->default('new')->index();
            $table->timestamps();
            $table->index(['qr_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishes');
    }
};
