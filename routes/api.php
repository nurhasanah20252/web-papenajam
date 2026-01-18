<?php

use App\Http\Controllers\Api\Sipp\SippCaseController;
use App\Http\Controllers\Api\Sipp\SippScheduleController;
use App\Http\Controllers\Api\Sipp\SippSyncController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SIPP API Routes
|--------------------------------------------------------------------------
|
| These routes handle SIPP (Sistem Informasi Penelusuran Perkara) data
| integration for Pengadilan Agama Penajam.
|
*/

Route::prefix('sipp')->name('sipp.')->group(function (): void {
    /*
    |--------------------------------------------------------------------------
    | Schedule Endpoints
    |--------------------------------------------------------------------------
    */
    Route::prefix('schedules')->name('schedules.')->group(function (): void {
        Route::get('/', [SippScheduleController::class, 'index'])->name('index');
        Route::get('/today', [SippScheduleController::class, 'today'])->name('today');
        Route::get('/upcoming', [SippScheduleController::class, 'upcoming'])->name('upcoming');
        Route::get('/calendar', [SippScheduleController::class, 'calendar'])->name('calendar');
        Route::get('/{id}', [SippScheduleController::class, 'show'])->name('show');
    });

    /*
    |--------------------------------------------------------------------------
    | Case Endpoints
    |--------------------------------------------------------------------------
    */
    Route::prefix('cases')->name('cases.')->group(function (): void {
        Route::get('/', [SippCaseController::class, 'index'])->name('index');
        Route::get('/statistics', [SippCaseController::class, 'statistics'])->name('statistics');
        Route::get('/{id}', [SippCaseController::class, 'show'])->name('show');
    });

    /*
    |--------------------------------------------------------------------------
    | Sync Endpoints
    |--------------------------------------------------------------------------
    */
    Route::prefix('sync')->name('sync.')->group(function (): void {
        Route::post('/trigger', [SippSyncController::class, 'trigger'])->name('trigger');
        Route::get('/status', [SippSyncController::class, 'status'])->name('status');
        Route::get('/history', [SippSyncController::class, 'history'])->name('history');
        Route::delete('/cache', [SippSyncController::class, 'clearCache'])->name('cache.clear');
    });
});
