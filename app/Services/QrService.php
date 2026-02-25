<?php

namespace App\Services;

use App\Models\Qr;
use App\Models\User;
use Illuminate\Support\Str;

class QrService
{
    public function create(array $data, ?User $user): Qr
    {
        return Qr::query()->create([
            'token' => $this->generateUniqueToken(),
            'name' => $data['name'] ?? null,
            'status' => $data['status'] ?? 'active',
            'created_by' => $user?->id,
        ]);
    }

    public function update(Qr $qr, array $data): Qr
    {
        $qr->update([
            'name' => $data['name'] ?? null,
            'status' => $data['status'] ?? $qr->status,
        ]);

        return $qr->refresh();
    }

    public function generateUniqueToken(int $length = 12): string
    {
        do {
            $token = Str::upper(Str::random($length));
        } while (Qr::query()->where('token', $token)->exists());

        return $token;
    }
}
