<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qrs', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();
            $table->string('name')->nullable();
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qrs');
    }
};
