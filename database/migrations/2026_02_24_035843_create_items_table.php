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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            // FK is added in a later migration to avoid ordering issues if qrs table is created after items.
            $table->foreignId('qr_id');
            $table->string('name');
            $table->string('color', 7)->nullable();
            $table->decimal('balance_stock', 15, 4)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('items');
        Schema::enableForeignKeyConstraints();
    }
};
