<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_pins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_id')->constrained('qrs')->cascadeOnDelete();
            $table->string('pin_number');
            $table->boolean('is_used')->default(false)->index();
            $table->timestamps();

            $table->unique(['qr_id', 'pin_number']);
            $table->index(['qr_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_pins');
    }
};
