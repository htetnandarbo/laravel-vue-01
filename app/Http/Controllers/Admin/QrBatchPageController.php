<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QrBatch;
use App\Services\QrBatch\QrBatchSettingsNormalizer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class QrBatchPageController extends Controller
{
    public function index(): Response
    {
        $this->ensureAdmin();

        return Inertia::render('admin/qr-batches/Index', [
            'defaults' => [
                'size_mode' => QrBatchSettingsNormalizer::defaults()['size_mode'],
                'size_preset' => QrBatchSettingsNormalizer::defaults()['size_preset'],
                'size_mm' => QrBatchSettingsNormalizer::defaults()['size_mm'],
            ],
            'sizePresets' => QrBatchSettingsNormalizer::SIZE_PRESETS,
            'initialBatch' => $this->latestBatchPayload(),
        ]);
    }

    public function history(): Response
    {
        $this->ensureAdmin();

        $batches = QrBatch::query()
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/qr-batches/History', [
            'batches' => $this->paginated($batches, fn (QrBatch $batch) => $this->batchPayload($batch)),
        ]);
    }

    private function ensureAdmin(): void
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
    }

    private function latestBatchPayload(): ?array
    {
        $batch = QrBatch::query()->latest('id')->first();

        if (! $batch) {
            return null;
        }

        return $this->batchPayload($batch);
    }

    private function batchPayload(QrBatch $batch): array
    {
        $downloadAvailable = (bool) ($batch->pdf_path && Storage::disk('local')->exists($batch->pdf_path));

        return [
            'id' => $batch->id,
            'status' => $batch->status,
            'base_url' => $batch->base_url,
            'size_mode' => $batch->size_mode,
            'size_mm' => $batch->size_mm,
            'pdf_path' => $batch->pdf_path,
            'progress_current' => (int) ($batch->progress_current ?? 0),
            'progress_total' => (int) ($batch->progress_total ?? 0),
            'progress_percent' => (int) ($batch->progress_percent ?? 0),
            'status_message' => $batch->status_message,
            'download_available' => $downloadAvailable,
            'download_url' => $downloadAvailable ? route('admin.api.qr-batches.download', $batch) : null,
            'created_at' => optional($batch->created_at)->toISOString(),
            'updated_at' => optional($batch->updated_at)->toISOString(),
            'started_at' => optional($batch->started_at)->toISOString(),
            'finished_at' => optional($batch->finished_at)->toISOString(),
        ];
    }

    private function paginated(LengthAwarePaginator $paginator, callable $map): array
    {
        return [
            'data' => collect($paginator->items())->map($map)->values()->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'links' => collect($paginator->linkCollection())->map(fn ($link) => [
                    'url' => $link['url'],
                    'label' => $link['label'],
                    'active' => (bool) $link['active'],
                ])->values()->all(),
            ],
        ];
    }
}
