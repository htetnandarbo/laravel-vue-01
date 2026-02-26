<?php

namespace App\Services;

use App\Models\Qr;
use App\Models\Wish;
use Illuminate\Validation\ValidationException;

class WishService
{
    public function create(Qr $qr, array $data): Wish
    {
        return Wish::query()->create([
            'qr_id' => $qr->id,
            'message' => trim((string) $data['message']),
            'status' => 'pending',
        ]);
    }

    public function updateStatus(Wish $wish, string $status): Wish
    {
        if ($wish->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => 'This wish has already been reviewed.',
            ]);
        }

        $wish->update(['status' => $status]);

        return $wish->refresh();
    }
}
