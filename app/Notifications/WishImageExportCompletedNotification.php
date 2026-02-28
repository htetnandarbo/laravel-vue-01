<?php

namespace App\Notifications;

use App\Models\WishImageExport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class WishImageExportCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly WishImageExport $export)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isCompleted = $this->export->status === 'completed'
            && $this->export->file_path
            && Storage::disk('local')->exists($this->export->file_path);

        return [
            'type' => 'wish_image_export',
            'export_id' => $this->export->id,
            'qr_id' => $this->export->qr_id,
            'status' => $this->export->status,
            'total_images' => (int) $this->export->total_images,
            'message' => $isCompleted
                ? sprintf('Wish image export is ready (%d images).', (int) $this->export->total_images)
                : ($this->export->error_message ?: 'Wish image export failed.'),
            'download_url' => $isCompleted ? route('admin.wish-image-exports.download', $this->export) : null,
            'created_at' => optional($this->export->created_at)->toISOString(),
            'finished_at' => optional($this->export->finished_at)->toISOString(),
        ];
    }
}
