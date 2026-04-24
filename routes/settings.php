<?php

use App\Http\Controllers\Settings\NotificationController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\ProfileAvatarController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['request-logging', 'auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['request-logging', 'auth', 'verified'])->group(function () {
    Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance.edit');

    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');

    Route::get('settings/notifications', [NotificationController::class, 'edit'])->name('notifications.edit');
    Route::put('settings/notifications', [NotificationController::class, 'update'])->name('notifications.update');

    Route::middleware(['request-logging', 'auth', 'verified', 'active'])->group(function () {
        Route::post('/profile/avatar/presign', [ProfileAvatarController::class, 'presign'])
            ->name('profile.avatar.presign');
    });

    Route::post('/profile/avatar', [ProfileAvatarController::class, 'store'])->name('profile.avatar.store');

    Route::delete('/profile/avatar', [ProfileAvatarController::class, 'destroy'])
        ->name('profile.avatar.destroy');

});
