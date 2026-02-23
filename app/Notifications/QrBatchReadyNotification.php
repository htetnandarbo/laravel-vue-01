<?php

namespace App\Notifications;

use App\Models\QrBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QrBatchReadyNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly QrBatch $batch)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'qr_batch_ready',
            'title' => 'QR Batch Ready',
            'message' => "QR batch #{$this->batch->id} is ready to download.",
            'qr_batch_id' => $this->batch->id,
            'status' => $this->batch->status,
            'download_url' => route('admin.api.qr-batches.download', $this->batch),
            'page_url' => route('admin.qr-batches.index'),
            'created_at' => now()->toISOString(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line("QR batch #{$this->batch->id} is ready to download.")
            ->action('Open QR Batches', route('admin.qr-batches.index'));
    }
}
