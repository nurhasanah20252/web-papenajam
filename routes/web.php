<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('/about', function () {
    return Inertia::render('about');
})->name('about');

Route::get('/news', function () {
    return Inertia::render('news');
})->name('news');

Route::get('/news/{slug}', function (string $slug) {
    return Inertia::render('news-detail', [
        'slug' => $slug,
    ]);
})->name('news.show');

Route::get('/schedules', function () {
    return Inertia::render('schedules');
})->name('schedules');

Route::get('/documents', function () {
    return Inertia::render('documents');
})->name('documents');

Route::get('/pages/{slug}', [PageController::class, 'show'])->name('page.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
