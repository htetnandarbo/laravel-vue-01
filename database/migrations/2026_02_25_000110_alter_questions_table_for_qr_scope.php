<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('qr_id')->nullable()->after('id')->constrained('qrs')->cascadeOnDelete();
            $table->string('label')->nullable()->after('qr_id');
            $table->json('options')->nullable()->after('is_required');
            $table->integer('sort_order')->default(0)->after('options');
            $table->index(['qr_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['qr_id']);
            $table->dropIndex(['qr_id', 'sort_order']);
            $table->dropColumn(['qr_id', 'label', 'options', 'sort_order']);
        });
    }
};
