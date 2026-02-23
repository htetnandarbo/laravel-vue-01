<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\Api\QrBatchController as AdminApiQrBatchController;
use App\Http\Controllers\Admin\Api\NotificationController as AdminApiNotificationController;
use App\Http\Controllers\Admin\NotificationPageController;
use App\Http\Controllers\Admin\QrBatchPageController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route::get('/', [WelcomeController::class, 'index'])->name('home');
Route::get('/', fn () => Inertia::render('survey/Index'))->name('survey.demo');
Route::get('/wish-demo', fn () => Inertia::render('wish/Index'))->name('wish.demo');
Route::get('/spin-demo', fn () => Inertia::render('spin/Index'))->name('spin.demo');
Route::get('/thanks-demo', fn () => Inertia::render('thanks/Index'))->name('thanks.demo');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', UserController::class);

    Route::resource('plans', PlanController::class);

    Route::resource('questions', QuestionController::class);

    Route::get('/admin/qr-batches', [QrBatchPageController::class, 'index'])->name('admin.qr-batches.index');
    Route::get('/admin/notifications', [NotificationPageController::class, 'index'])->name('admin.notifications.index');

    Route::prefix('/api/admin')->name('admin.api.')->group(function () {
        Route::post('/qr-batches', [AdminApiQrBatchController::class, 'store'])->name('qr-batches.store');
        Route::get('/qr-batches/{qrBatch}', [AdminApiQrBatchController::class, 'show'])->name('qr-batches.show');
        Route::get('/qr-batches/{qrBatch}/download', [AdminApiQrBatchController::class, 'download'])->name('qr-batches.download');
        Route::get('/notifications', [AdminApiNotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/poll', [AdminApiNotificationController::class, 'poll'])->name('notifications.poll');
        Route::post('/notifications/mark-all-read', [AdminApiNotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
        Route::post('/notifications/{notification}/read', [AdminApiNotificationController::class, 'markRead'])->name('notifications.read');
    });
});



require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
