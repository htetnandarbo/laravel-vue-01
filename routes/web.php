<?php

use App\Http\Controllers\DashboardController;
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
});



require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
