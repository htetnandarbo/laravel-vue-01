<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $this->ensureAdmin();

        return response()->json([
            'notifications' => $this->mapNotifications(Auth::user()->notifications()->latest()->limit(20)->get()),
            'unread_count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    public function poll(): JsonResponse
    {
        $this->ensureAdmin();

        return response()->json([
            'notifications' => $this->mapNotifications(Auth::user()->unreadNotifications()->latest()->limit(10)->get()),
            'unread_count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(DatabaseNotification $notification): JsonResponse
    {
        $this->ensureAdmin();
        abort_unless($notification->notifiable_id === Auth::id() && $notification->notifiable_type === Auth::user()::class, 403);

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return response()->json(['ok' => true]);
    }

    public function markAllRead(): JsonResponse
    {
        $this->ensureAdmin();

        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }

    private function ensureAdmin(): void
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
    }

    private function mapNotifications($notifications): array
    {
        return $notifications->map(fn ($notification) => [
            'id' => $notification->id,
            'type' => class_basename($notification->type),
            'data' => $notification->data,
            'read_at' => optional($notification->read_at)->toISOString(),
            'created_at' => optional($notification->created_at)->toISOString(),
        ])->values()->all();
    }
}
