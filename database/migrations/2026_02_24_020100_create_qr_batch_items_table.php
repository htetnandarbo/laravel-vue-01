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
        Schema::create('qr_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_batch_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('token', 64)->unique();
            $table->text('url');
            $table->timestamps();

            $table->index(['qr_batch_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_batch_items');
    }
};
