<?php

namespace App\Jobs;

use App\Models\QrBatch;
use App\Models\User;
use App\Notifications\QrBatchReadyNotification;
use App\Services\QrBatch\QrBatchGeneratorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateQrBatchJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(public int $qrBatchId)
    {
    }

    public function handle(QrBatchGeneratorService $generator): void
    {
        $batch = QrBatch::find($this->qrBatchId);

        if (! $batch) {
            return;
        }

        $batch = $generator->generate($batch);

        User::query()
            ->where('role', 'admin')
            ->get()
            ->each
            ->notify(new QrBatchReadyNotification($batch));
    }

    public function failed(?Throwable $exception): void
    {
        $batch = QrBatch::find($this->qrBatchId);

        if ($exception) {
            report($exception);

            Log::error('QR batch generation job failed', [
                'qr_batch_id' => $this->qrBatchId,
                'exception_class' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
        } else {
            Log::error('QR batch generation job failed without exception', [
                'qr_batch_id' => $this->qrBatchId,
            ]);
        }

        if (! $batch) {
            return;
        }

        $batch->update([
            'status' => 'failed',
            'status_message' => $exception?->getMessage() ?: 'QR batch generation failed.',
            'finished_at' => now(),
        ]);
    }
}
