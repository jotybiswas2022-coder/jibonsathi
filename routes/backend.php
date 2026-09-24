<?php

use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\MessageController;
use App\Http\Controllers\Backend\ProfileController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\SuccessStoryController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\VerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
|
| Completely separate control panel mounted at /admin. Every route is
| protected by the `admin` middleware (authentication + role check).
|
*/

Route::prefix('admin')->name('backend.')->middleware(['web', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::post('/{user}/verify', [UserController::class, 'verify'])->name('verify');
        Route::post('/{user}/suspend', [UserController::class, 'suspend'])->name('suspend');
        Route::post('/{user}/activate', [UserController::class, 'activate'])->name('activate');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('profiles')->name('profiles.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/{profile}', [ProfileController::class, 'show'])->name('show');
        Route::post('/{profile}/moderate', [ProfileController::class, 'moderate'])->name('moderate');
        Route::delete('/{profile}/photos/{photo}', [ProfileController::class, 'destroyPhoto'])->name('photos.destroy');
    });

    Route::prefix('verifications')->name('verification.')->group(function () {
        Route::get('/', [VerificationController::class, 'index'])->name('index');
        Route::get('/{verification}', [VerificationController::class, 'show'])->name('show');
        Route::get('/{verification}/document', [VerificationController::class, 'document'])->name('document');
        Route::post('/{verification}/decide', [VerificationController::class, 'decide'])->name('decide');
        Route::delete('/{verification}', [VerificationController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/{report}', [ReportController::class, 'show'])->name('show');
        Route::post('/{report}/decide', [ReportController::class, 'decide'])->name('decide');
    });

    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/{conversation}', [MessageController::class, 'show'])->name('show');
    });

    Route::prefix('success-stories')->name('success-stories.')->group(function () {
        Route::get('/', [SuccessStoryController::class, 'index'])->name('index');
        Route::get('/create', [SuccessStoryController::class, 'create'])->name('create');
        Route::post('/', [SuccessStoryController::class, 'store'])->name('store');
        Route::get('/{story}/edit', [SuccessStoryController::class, 'edit'])->name('edit');
        Route::put('/{story}', [SuccessStoryController::class, 'update'])->name('update');
        Route::post('/{story}/toggle-publish', [SuccessStoryController::class, 'togglePublish'])->name('toggle-publish');
        Route::delete('/{story}', [SuccessStoryController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'edit'])->name('index');
        Route::post('/', [SettingController::class, 'update'])->name('update');
    });
});