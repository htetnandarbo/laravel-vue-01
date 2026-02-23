<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NotificationPageController extends Controller
{
    public function index(): Response
    {
        $this->ensureAdmin();

        return Inertia::render('admin/notifications/Index', [
            'notifications' => $this->mapNotifications(Auth::user()->notifications()->latest()->limit(50)->get()),
        ]);
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
