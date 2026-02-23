<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QrBatch;
use App\Services\QrBatch\QrBatchSettingsNormalizer;
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
            'defaults' => QrBatchSettingsNormalizer::defaults(),
            'sizePresets' => QrBatchSettingsNormalizer::SIZE_PRESETS,
            'initialBatch' => $this->latestBatchPayload(),
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

        $downloadAvailable = (bool) ($batch->pdf_path && Storage::disk('local')->exists($batch->pdf_path));

        return [
            'id' => $batch->id,
            'quantity' => $batch->quantity,
            'status' => $batch->status,
            'base_url' => $batch->base_url,
            'page_format' => $batch->page_format,
            'margin_mm' => $batch->margin_mm,
            'gap_mm' => $batch->gap_mm,
            'cols' => $batch->cols,
            'rows' => $batch->rows,
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
}
