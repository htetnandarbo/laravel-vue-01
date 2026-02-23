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
        Schema::create('qr_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('quantity');
            $table->string('status', 32)->default('pending');
            $table->text('base_url');
            $table->string('page_format', 16)->default('A4');
            $table->decimal('margin_mm', 6, 2)->default(8);
            $table->decimal('gap_mm', 6, 2)->default(4);
            $table->unsignedSmallInteger('cols')->default(4);
            $table->unsignedSmallInteger('rows')->default(6);
            $table->string('size_mode', 16)->default('preset');
            $table->decimal('size_mm', 6, 2);
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_batches');
    }
};
