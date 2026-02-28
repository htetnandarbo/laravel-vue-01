<?php

namespace App\Jobs;

use App\Models\Wish;
use App\Models\WishImageExport;
use App\Notifications\WishImageExportCompletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

class GenerateWishImagesZip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $wishImageExportId)
    {
    }

    public function handle(): void
    {
        $export = WishImageExport::query()->with(['qr', 'user'])->find($this->wishImageExportId);

        if (! $export || ! $export->user) {
            return;
        }

        $export->update([
            'status' => 'processing',
            'error_message' => null,
            'started_at' => now(),
            'finished_at' => null,
            'file_path' => null,
            'total_images' => 0,
        ]);

        try {
            if (! class_exists(ZipArchive::class)) {
                throw new RuntimeException('ZipArchive extension is required to export wish images.');
            }

            $relativeDirectory = sprintf('private/wish-exports/qr-%d', $export->qr_id);
            $relativePath = sprintf(
                '%s/wish-images-export-%d-%s.zip',
                $relativeDirectory,
                $export->id,
                now()->format('YmdHis')
            );

            Storage::disk('local')->makeDirectory($relativeDirectory);

            $zipPath = Storage::disk('local')->path($relativePath);
            $zip = new ZipArchive();
            $result = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if ($result !== true) {
                throw new RuntimeException('Unable to create ZIP archive for wish images.');
            }

            $included = 0;

            Wish::query()
                ->where('qr_id', $export->qr_id)
                ->where('status', 'accepted')
                ->where('is_downloaded', false)
                ->whereNotNull('image')
                ->orderBy('id')
                ->chunkById(1000, function ($wishes) use (&$zip, &$included) {
                    $processedIds = [];

                    foreach ($wishes as $wish) {
                        if ($this->addWishImageToZip($wish, $zip)) {
                            $processedIds[] = $wish->id;
                            $included++;
                        }
                    }

                    if ($processedIds !== []) {
                        Wish::query()
                            ->whereIn('id', $processedIds)
                            ->update(['is_downloaded' => true]);
                    }
                });

            $zip->close();

            if ($included === 0) {
                Storage::disk('local')->delete($relativePath);

                $export->update([
                    'status' => 'failed',
                    'error_message' => 'No accepted and not-yet-downloaded wish images were found for this QR.',
                    'finished_at' => now(),
                    'total_images' => 0,
                ]);

                $export->user->notify(new WishImageExportCompletedNotification($export->fresh()));

                return;
            }

            $export->update([
                'status' => 'completed',
                'file_path' => $relativePath,
                'total_images' => $included,
                'error_message' => null,
                'finished_at' => now(),
            ]);

            $export->user->notify(new WishImageExportCompletedNotification($export->fresh()));
        } catch (Throwable $exception) {
            if (! empty($relativePath ?? null)) {
                Storage::disk('local')->delete($relativePath);
            }

            $export->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            $export->user->notify(new WishImageExportCompletedNotification($export->fresh()));

            throw $exception;
        }
    }

    private function addWishImageToZip(Wish $wish, ZipArchive $zip): bool
    {
        $normalized = $this->normalizeStoredPath((string) $wish->image);

        if ($normalized === '') {
            return false;
        }

        $archiveName = sprintf('wish-%d-%s', $wish->id, basename($normalized));

        $localCandidates = [
            public_path($normalized),
            storage_path('app/public/'.$normalized),
            storage_path('app/'.$normalized),
        ];

        foreach ($localCandidates as $candidate) {
            if (is_file($candidate)) {
                return $zip->addFile($candidate, $archiveName);
            }
        }

        if (Storage::disk('idrive')->exists($normalized)) {
            $contents = Storage::disk('idrive')->get($normalized);
            if ($contents !== null && $contents !== false) {
                return $zip->addFromString($archiveName, $contents);
            }
        }

        return false;
    }

    private function normalizeStoredPath(string $storedPath): string
    {
        $raw = trim($storedPath);
        if ($raw === '') {
            return '';
        }

        if (! Str::startsWith($raw, ['http://', 'https://'])) {
            return ltrim($raw, '/\\');
        }

        $path = (string) parse_url($raw, PHP_URL_PATH);
        $path = ltrim($path, '/\\');

        if ($path === '') {
            return '';
        }

        $bucket = trim((string) config('filesystems.disks.idrive.bucket'));
        if ($bucket !== '' && Str::startsWith($path, $bucket.'/')) {
            $path = Str::after($path, $bucket.'/');
        }

        return $path;
    }
}
