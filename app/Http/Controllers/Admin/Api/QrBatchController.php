<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQrBatchRequest;
use App\Models\QrBatch;
use App\Services\QrBatch\QrBatchGeneratorService;
use App\Services\QrBatch\QrBatchSettingsNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QrBatchController extends Controller
{
    public function __construct(
        private readonly QrBatchSettingsNormalizer $normalizer,
        private readonly QrBatchGeneratorService $generator,
    ) {
    }

    public function store(StoreQrBatchRequest $request): JsonResponse
    {
        $this->ensureAdmin();

        $normalized = $this->normalizer->normalize($request->validated());
        $batch = QrBatch::create(array_merge($normalized, [
            'status' => 'pending',
            'progress_current' => 0,
            'progress_total' => 1000,
            'progress_percent' => 0,
            'status_message' => 'Queued',
        ]));

        $batch = $this->generator->generate($batch);

        return response()->json([
            'message' => 'QR generated successfully.',
            'batch' => $this->toApiPayload($batch),
        ]);
    }

    public function show(QrBatch $qrBatch): JsonResponse
    {
        $this->ensureAdmin();

        return response()->json([
            'batch' => $this->toApiPayload($qrBatch),
        ]);
    }

    public function download(QrBatch $qrBatch): BinaryFileResponse
    {
        $this->ensureAdmin();

        if (! $qrBatch->pdf_path || ! Storage::disk('local')->exists($qrBatch->pdf_path)) {
            throw ValidationException::withMessages([
                'download' => 'The batch file is not ready for download.',
            ]);
        }

        return response()->download(
            Storage::disk('local')->path($qrBatch->pdf_path),
            basename($qrBatch->pdf_path)
        );
    }

    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }

    private function toApiPayload(QrBatch $batch): array
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
}
