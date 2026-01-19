<?php

use App\Http\Controllers\BudgetTransparencyController;
use App\Http\Controllers\CaseStatisticsController;
use App\Http\Controllers\CourtScheduleController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PageBuilderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PpidController;
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

Route::prefix('news')->name('news.')->group(function () {
    Route::get('/', [NewsController::class, 'index'])->name('index');
    Route::get('/category/{slug}', [NewsController::class, 'category'])->name('category');
    Route::get('/tag/{tag}', [NewsController::class, 'tag'])->name('tag');
    Route::get('/rss', [NewsController::class, 'rss'])->name('rss');
    Route::get('/{slug}', [NewsController::class, 'show'])->name('show');
});

Route::get('/schedules', [CourtScheduleController::class, 'index'])->name('schedules');
Route::get('/jadwal-sidang', [CourtScheduleController::class, 'index'])->name('jadwal-sidang');
Route::get('/jadwal-sidang/{id}', [CourtScheduleController::class, 'show'])->name('jadwal-sidang.show');

Route::prefix('documents')->name('documents.')->group(function () {
    Route::get('/', [DocumentController::class, 'index'])->name('index');
    Route::get('/{slug}', [DocumentController::class, 'show'])->name('show');
    Route::get('/{slug}/download', [DocumentController::class, 'download'])->name('download');
    Route::get('/{slug}/versions/{version}/download', [DocumentController::class, 'downloadVersion'])->name('versions.download');
});

Route::get('/pages/{slug}', [PageController::class, 'show'])->name('page.show');

Route::prefix('ppid')->name('ppid.')->group(function () {
    Route::get('/', [PpidController::class, 'index'])->name('index');
    Route::get('/form', [PpidController::class, 'create'])->name('form');
    Route::post('/', [PpidController::class, 'store'])->name('store');
    Route::get('/tracking', [PpidController::class, 'tracking'])->name('tracking');
    Route::get('/my-requests', [PpidController::class, 'myRequests'])->name('my-requests')->middleware('auth');
    Route::get('/{id}', [PpidController::class, 'show'])->name('show')->middleware('auth');
});

Route::prefix('transparansi-anggaran')->name('budget-transparency.')->group(function () {
    Route::get('/', [BudgetTransparencyController::class, 'index'])->name('index');
});

Route::prefix('statistik-perkara')->name('case-statistics.')->group(function () {
    Route::get('/', [CaseStatisticsController::class, 'index'])->name('index');
    Route::get('/export', [CaseStatisticsController::class, 'export'])->name('export');
});

Route::prefix('api/menus')->name('api.menus.')->group(function () {
    Route::get('/{location}', [MenuController::class, 'getByLocation'])->name('location');
    Route::get('/{menu}/items', [MenuController::class, 'items'])->name('items');
    Route::post('/{menu}/reorder', [MenuController::class, 'reorder'])->name('reorder');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::prefix('builder')->name('builder.')->group(function () {
        Route::get('/pages/{page}/edit', [PageBuilderController::class, 'edit'])->name('edit');
        Route::get('/pages/{page}', [PageBuilderController::class, 'show'])->name('show');
        Route::put('/pages/{page}', [PageBuilderController::class, 'update'])->name('update');
        Route::post('/pages/{page}/restore/{version}', [PageBuilderController::class, 'restoreVersion'])->name('restore');
        Route::post('/templates', [PageBuilderController::class, 'saveTemplate'])->name('templates.save');
        Route::post('/pages/{page}/blocks/duplicate', [PageBuilderController::class, 'duplicateBlock'])->name('duplicate-block');
        Route::delete('/pages/{page}/blocks/delete', [PageBuilderController::class, 'deleteBlock'])->name('delete-block');
        Route::post('/pages/{page}/preview', [PageBuilderController::class, 'preview'])->name('preview');
        Route::get('/block-types', [PageBuilderController::class, 'blockTypes'])->name('block-types');
    });

    Route::prefix('admin/menus')->name('admin.menus.')->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('index')->middleware('permission:menus.viewAny');
        Route::get('/{menu}', [MenuController::class, 'show'])->name('show')->middleware('permission:menus.view');
        Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('edit')->middleware('permission:menus.update');
        Route::put('/{menu}/structure', [MenuController::class, 'updateStructure'])->name('update-structure')->middleware('permission:menus.update');
        Route::put('/{menu}/locations', [MenuController::class, 'storeLocation'])->name('store-locations')->middleware('permission:menus.update');

        Route::prefix('/{menu}/items')->name('items.')->group(function () {
            Route::post('/', [MenuItemController::class, 'store'])->name('store')->middleware('permission:menuItems.create');
            Route::get('/{item}', [MenuItemController::class, 'show'])->name('show')->middleware('permission:menuItems.view');
            Route::put('/{item}', [MenuItemController::class, 'update'])->name('update')->middleware('permission:menuItems.update');
            Route::delete('/{item}', [MenuItemController::class, 'destroy'])->name('destroy')->middleware('permission:menuItems.delete');
        });
    });
});

require __DIR__.'/settings.php';
