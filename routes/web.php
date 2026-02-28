<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\Api\QrBatchController as AdminApiQrBatchController;
use App\Http\Controllers\Admin\FormResponseController as AdminFormResponseController;
use App\Http\Controllers\Admin\QrController as AdminQrController;
use App\Http\Controllers\Admin\QrItemController as AdminQrItemController;
use App\Http\Controllers\Admin\QrPinController as AdminQrPinController;
use App\Http\Controllers\Admin\QrQuestionController as AdminQrQuestionController;
use App\Http\Controllers\Admin\QrBatchPageController;
use App\Http\Controllers\Admin\StockTransactionController as AdminStockTransactionController;
use App\Http\Controllers\Admin\WishController as AdminWishController;
use App\Http\Controllers\Admin\WishImageExportController as AdminWishImageExportController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PublicQrController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WelcomeController;
use App\Models\Qr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route::get('/', [WelcomeController::class, 'index'])->name('home');
$resolveActiveQrToken = function (Request $request): ?string {
    $token = trim((string) ($request->query('qr') ?? $request->query('token') ?? ''));
    if ($token === '') {
        return null;
    }

    return Qr::query()->where('token', $token)->where('status', 'active')->exists() ? $token : null;
};

$qrRequiredPage = function (string $message = 'You need a valid QR link to enter this page.') {
    return Inertia::render('Public/QrRequired', [
        'title' => 'QR Required',
        'message' => $message,
    ]);
};

Route::get('/', function (Request $request) use ($resolveActiveQrToken, $qrRequiredPage) {
    $token = $resolveActiveQrToken($request);

    if (! $token) {
        return $qrRequiredPage();
    }

    return redirect()->route('public.qr.entry', ['qr' => $token]);
})->name('survey.demo');

Route::get('/wish-demo', function (Request $request) use ($resolveActiveQrToken, $qrRequiredPage) {
    $token = $resolveActiveQrToken($request);

    if (! $token) {
        return $qrRequiredPage();
    }

    return Inertia::render('wish/Index', ['qrToken' => $token]);
})->name('wish.demo');

Route::get('/spin-demo', function (Request $request) use ($resolveActiveQrToken, $qrRequiredPage) {
    $token = $resolveActiveQrToken($request);

    if (! $token) {
        return $qrRequiredPage();
    }

    $qr = Qr::query()
        ->where('token', $token)
        ->where('status', 'active')
        ->with(['items' => fn ($query) => $query->where('balance_stock', '>', 0)])
        ->first();

    return Inertia::render('spin/Index', [
        'qrToken' => $token,
        'prizes' => $qr?->items->map(fn ($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'color' => $item->color,
        ])->values()->all() ?? [],
    ]);
})->name('spin.demo');

Route::get('/thanks-demo', function (Request $request) use ($resolveActiveQrToken, $qrRequiredPage) {
    $token = $resolveActiveQrToken($request);

    if (! $token) {
        return $qrRequiredPage();
    }

    $qr = Qr::query()
        ->where('token', $token)
        ->where('status', 'active')
        ->first();

    $itemId = (int) $request->query('item', 0);
    $item = null;

    if ($qr && $itemId > 0) {
        $item = $qr->items()->whereKey($itemId)->first();
    }

    return Inertia::render('thanks/Index', [
        'qrToken' => $token,
        'winnerItem' => $item ? [
            'id' => $item->id,
            'name' => $item->name,
            'color' => $item->color,
            'image' => $item->image ?? null,
        ] : null,
    ]);
})->name('thanks.demo');

Route::get('/check-pin/{qr}/{pin}', [AdminQrPinController::class, 'checkPin'])->name('check-pin');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', UserController::class);

    Route::resource('questions', QuestionController::class);

    Route::resource('items', ItemController::class);
    Route::post('/items/{token}/{id}/stock', [ItemController::class, 'stockUpdate'])->name('items.stock.update');

    Route::get('/admin/qr-batches', [QrBatchPageController::class, 'index'])->name('admin.qr-batches.index');
    Route::get('/admin/qr-batches/history', [QrBatchPageController::class, 'history'])->name('admin.qr-batches.history');
    Route::prefix('/api/admin')->name('admin.api.')->group(function () {
        Route::post('/qr-batches', [AdminApiQrBatchController::class, 'store'])->name('qr-batches.store');
        Route::get('/qr-batches/{qrBatch}', [AdminApiQrBatchController::class, 'show'])->name('qr-batches.show');
        Route::get('/qr-batches/{qrBatch}/download', [AdminApiQrBatchController::class, 'download'])->name('qr-batches.download');
    });
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/qrs', [AdminQrController::class, 'index'])->name('qrs.index');
    Route::post('/qrs', [AdminQrController::class, 'store'])->name('qrs.store');
    Route::patch('/qrs/{qr}', [AdminQrController::class, 'update'])->name('qrs.update');
    Route::delete('/qrs/{qr}', [AdminQrController::class, 'destroy'])->name('qrs.destroy');
    Route::get('/qrs/{qr}', [AdminQrController::class, 'show'])->name('qrs.show');
    Route::get('/qrs/{qr}/questions', [AdminQrController::class, 'questions'])->name('qrs.questions');
    Route::get('/qrs/{qr}/items', [AdminQrController::class, 'items'])->name('qrs.items');
    Route::get('/qrs/{qr}/stock', [AdminQrController::class, 'stock'])->name('qrs.stock');
    Route::get('/qrs/{qr}/responses', [AdminQrController::class, 'responses'])->name('qrs.responses');
    Route::get('/qrs/{qr}/wishes', [AdminQrController::class, 'wishes'])->name('qrs.wishes');
    Route::get('/qrs/{qr}/pins', [AdminQrController::class, 'pins'])->name('qrs.pins');

    Route::post('/qrs/{qr}/questions', [AdminQrQuestionController::class, 'store'])->name('qrs.questions.store');
    Route::patch('/questions/{question}', [AdminQrQuestionController::class, 'update'])->name('questions.update');
    Route::delete('/questions/{question}', [AdminQrQuestionController::class, 'destroy'])->name('questions.destroy');

    Route::post('/qrs/{qr}/items', [AdminQrItemController::class, 'store'])->name('qrs.items.store');
    Route::post('/qrs/{qr}/pins', [AdminQrPinController::class, 'store'])->name('qrs.pins.store');
    Route::get('/qrs/{qr}/pins/export', [AdminQrPinController::class, 'export'])->name('qrs.pins.export');
    Route::patch('/items/{item}', [AdminQrItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [AdminQrItemController::class, 'destroy'])->name('items.destroy');

    Route::post('/qrs/{qr}/stock-transactions', [AdminStockTransactionController::class, 'store'])->name('qrs.stock-transactions.store');

    Route::get('/qrs/{qr}/responses-legacy', [AdminFormResponseController::class, 'index'])->name('qrs.responses.index');
    Route::get('/responses/{response}', [AdminFormResponseController::class, 'show'])->name('responses.show');
    Route::patch('/responses/{response}', [AdminFormResponseController::class, 'update'])->name('responses.update');

    Route::get('/qrs/{qr}/wishes-legacy', [AdminWishController::class, 'index'])->name('qrs.wishes.index');
    Route::patch('/wishes/{wish}', [AdminWishController::class, 'update'])->name('wishes.update');
    Route::get('/qrs/{qr}/wishes/image-exports', [AdminWishImageExportController::class, 'index'])->name('qrs.wishes.image-exports.index');
    Route::post('/qrs/{qr}/wishes/image-exports', [AdminWishImageExportController::class, 'store'])->name('qrs.wishes.image-exports.store');
    Route::get('/wish-image-exports/{wishImageExport}/download', [AdminWishImageExportController::class, 'download'])->name('wish-image-exports.download');
});

Route::get('/qr', [PublicQrController::class, 'entry'])->name('public.qr.entry');
Route::get('/qr={token}', fn (string $token) => redirect()->route('public.qr.entry', ['qr' => $token]));
Route::get('/qr/{token}', [PublicQrController::class, 'show'])->name('public.qr.show');
Route::post('/qr/{token}/submit', [PublicQrController::class, 'submit'])->name('public.qr.submit');
Route::post('/qr/{token}/wish', [PublicQrController::class, 'wish'])->name('public.qr.wish');
Route::post('/qr/{token}/wish-card-image', [PublicQrController::class, 'storeWishCardImage'])->name('public.qr.wish-card-image');



require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
