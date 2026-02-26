<?php

namespace App\Services;

use App\Models\Qr;
use App\Models\QrPin;
use Illuminate\Support\Facades\DB;

class QrPinService
{
    public function generate(Qr $qr, int $count): void
    {
        DB::transaction(function () use ($qr, $count) {
            $rows = [];

            for ($i = 0; $i < $count; $i++) {
                $rows[] = [
                    'qr_id' => $qr->id,
                    'pin_number' => $this->generateUniquePin($qr),
                    'is_used' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            QrPin::query()->insert($rows);
        });
    }

    private function generateUniquePin(Qr $qr): string
    {
        do {
            $pin = (string) random_int(100000, 999999);
        } while (
            QrPin::query()
                ->where('qr_id', $qr->id)
                ->where('pin_number', $pin)
                ->exists()
        );

        return $pin;
    }
}
