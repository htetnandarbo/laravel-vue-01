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
        Schema::table('qr_batches', function (Blueprint $table) {
            $table->unsignedInteger('progress_current')->default(0)->after('pdf_path');
            $table->unsignedInteger('progress_total')->default(100)->after('progress_current');
            $table->unsignedTinyInteger('progress_percent')->default(0)->after('progress_total');
            $table->string('status_message')->nullable()->after('progress_percent');
            $table->timestamp('started_at')->nullable()->after('status_message');
            $table->timestamp('finished_at')->nullable()->after('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qr_batches', function (Blueprint $table) {
            $table->dropColumn([
                'progress_current',
                'progress_total',
                'progress_percent',
                'status_message',
                'started_at',
                'finished_at',
            ]);
        });
    }
};
