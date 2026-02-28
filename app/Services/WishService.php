<?php

namespace App\Services;

use App\Models\Qr;
use App\Models\Wish;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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

    public function storeCardImage(Qr $qr, Wish $wish, string $imageData): ?Wish
    {
        $prefix = 'data:image/png;base64,';
        if (! str_starts_with($imageData, $prefix)) {
            throw ValidationException::withMessages([
                'image' => 'Invalid image data.',
            ]);
        }

        $binary = base64_decode(substr($imageData, strlen($prefix)), true);

        if ($binary === false) {
            throw ValidationException::withMessages([
                'image' => 'Image encoding is invalid.',
            ]);
        }

        $directory = public_path('wish-cards');
        File::ensureDirectoryExists($directory);

        $filename = sprintf(
            'wish-qr%s-%s-%s.png',
            $qr->id,
            now()->format('YmdHis'),
            Str::lower(Str::random(8))
        );

        File::put($directory.DIRECTORY_SEPARATOR.$filename, $binary);

        if ($wish) {
            $wish->update([
                'image' => 'wish-cards/'.$filename,
            ]);
        }

        return $wish;
    }
}
