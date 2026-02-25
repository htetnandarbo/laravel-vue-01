<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (! Schema::hasColumn('items', 'qr_id')) {
                $table->foreignId('qr_id')->nullable()->after('id');
            }

            if (! Schema::hasColumn('items', 'sku')) {
                $table->string('sku')->nullable()->after('name');
            }
        });

        try {
            Schema::table('items', function (Blueprint $table) {
                $table->foreign('qr_id')->references('id')->on('qrs')->cascadeOnDelete();
            });
        } catch (\Throwable) {
            // FK may already exist (or DB is in a partial/custom state).
        }

        // Avoid duplicate index creation when the schema was already customized.
        try {
            Schema::table('items', function (Blueprint $table) {
                $table->index(['qr_id', 'name']);
            });
        } catch (\Throwable) {
            // Index already exists or columns unavailable in a custom schema state.
        }
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            try {
                $table->dropIndex(['qr_id', 'name']);
            } catch (\Throwable) {
            }

            if (Schema::hasColumn('items', 'sku')) {
                $table->dropColumn('sku');
            }

            try {
                $table->dropForeign(['qr_id']);
            } catch (\Throwable) {
            }

            // Keep qr_id if it belongs to the base items table in this project variant.
        });
    }
};
