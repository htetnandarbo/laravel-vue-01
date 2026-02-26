<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('items')) {
            return;
        }

        Schema::table('items', function (Blueprint $table) {
            if (! Schema::hasColumn('items', 'color')) {
                $table->string('color', 7)->nullable()->after('name');
            }
        });

        if (Schema::hasColumn('items', 'color')) {
            $palette = [
                '#f59e0b', '#fcd34d', '#fb7185', '#c084fc', '#60a5fa',
                '#34d399', '#fdba74', '#f9a8d4', '#93c5fd', '#a7f3d0',
            ];

            $groups = DB::table('items')
                ->select('id', 'qr_id')
                ->whereNull('color')
                ->orderBy('qr_id')
                ->orderBy('id')
                ->get()
                ->groupBy('qr_id');

            foreach ($groups as $items) {
                foreach (array_values($items->all()) as $index => $item) {
                    // Step by 3 through the palette to spread similar colors farther apart.
                    $paletteIndex = ($index * 3) % count($palette);
                    DB::table('items')
                        ->where('id', $item->id)
                        ->update(['color' => $palette[$paletteIndex]]);
                }
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('items')) {
            return;
        }

        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'color')) {
                $table->dropColumn('color');
            }
        });
    }
};
