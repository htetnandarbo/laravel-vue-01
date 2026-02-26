<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wishes')) {
            return;
        }

        // Normalize existing values first.
        DB::table('wishes')->where('status', 'new')->update(['status' => 'pending']);
        DB::table('wishes')->where('status', 'seen')->update(['status' => 'accepted']);
        DB::table('wishes')->where('status', 'done')->update(['status' => 'accepted']);

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE wishes MODIFY status ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('wishes')) {
            return;
        }

        DB::table('wishes')->where('status', 'pending')->update(['status' => 'new']);
        DB::table('wishes')->where('status', 'accepted')->update(['status' => 'done']);
        DB::table('wishes')->where('status', 'rejected')->update(['status' => 'new']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE wishes MODIFY status ENUM('new','seen','done') NOT NULL DEFAULT 'new'");
        }
    }
};
