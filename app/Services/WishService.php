<?php

namespace App\Services;

use App\Models\Qr;
use App\Models\Wish;

class WishService
{
    public function create(Qr $qr, array $data): Wish
    {
        return Wish::query()->create([
            'qr_id' => $qr->id,
            'message' => trim((string) $data['message']),
            'status' => 'new',
        ]);
    }

    public function updateStatus(Wish $wish, string $status): Wish
    {
        $wish->update(['status' => $status]);

        return $wish->refresh();
    }
}
