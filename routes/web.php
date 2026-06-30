<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Dashboard\AppearanceController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\ItemController;
use App\Http\Controllers\Dashboard\LinkController;
use App\Http\Controllers\Dashboard\MyWebsiteController;
use App\Http\Controllers\Dashboard\PageController;
use App\Http\Controllers\Dashboard\QrCodeController;
use App\Http\Controllers\Dashboard\StatsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\PublicTrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}/suspend', [UserManagementController::class, 'suspend'])->name('users.suspend');
    Route::patch('/users/{user}/activate', [UserManagementController::class, 'activate'])->name('users.activate');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/qr/download', [QrCodeController::class, 'download'])->name('dashboard.qr.download');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard/my-website', [MyWebsiteController::class, 'index'])->name('dashboard.my-website.index');
    Route::post('/dashboard/my-website', [MyWebsiteController::class, 'store'])->name('dashboard.my-website.store');
    Route::match(['put', 'patch'], '/dashboard/my-website', [MyWebsiteController::class, 'update'])->name('dashboard.my-website.update');

    Route::prefix('/dashboard/links')->name('dashboard.links.')->controller(LinkController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::post('/reorder', 'reorder')->name('reorder');
        Route::get('/{link}/edit', 'edit')->name('edit');
        Route::put('/{link}', 'update')->name('update');
        Route::delete('/{link}', 'destroy')->name('destroy');
    });

    Route::redirect('/links', '/dashboard/links')->name('links.index');


    Route::prefix('/dashboard/categories')->name('dashboard.categories.')->controller(CategoryController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{category}/edit', 'edit')->name('edit');
        Route::put('/{category}', 'update')->name('update');
        Route::delete('/{category}', 'destroy')->name('destroy');
        Route::patch('/{category}/toggle', 'toggle')->name('toggle');
        Route::post('/reorder', 'reorder')->name('reorder');
    });
    Route::prefix('/dashboard/items')->name('dashboard.items.')->controller(ItemController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('', 'store')->name('store');
        Route::get('/{item}/edit', 'edit')->name('edit');
        Route::put('/{item}', 'update')->name('update');
        Route::delete('/{item}', 'destroy')->name('destroy');
        Route::patch('/{item}/toggle', 'toggle')->name('toggle');
        Route::post('reorder', 'reorder')->name('reorder');
    });


    Route::get('/dashboard/stats', [StatsController::class, 'index'])->name('dashboard.stats.index');

    Route::get('/dashboard/appearance', [AppearanceController::class, 'index'])->name('dashboard.appearance.index');
    Route::match(['put', 'patch'], '/dashboard/appearance', [AppearanceController::class, 'update'])->name('dashboard.appearance.update');
    Route::prefix('/pages')->name('pages.')->controller(PageController::class)->group(function () {
        Route::get('/bio1/{slug}', 'show')->name('bio1');
    });
});

require __DIR__.'/auth.php';

Route::post('/track/link/{link}', [PublicTrackingController::class, 'link'])->name('track.link');
Route::post('/track/item-order/{item}', [PublicTrackingController::class, 'itemOrder'])->name('track.item-order');
Route::post('/track/order-attempt/{page}/{channel}', [PublicTrackingController::class, 'orderAttempt'])->name('track.order-attempt');

Route::get('/{slug}', [PublicPageController::class, 'show'])->name('public.show');
