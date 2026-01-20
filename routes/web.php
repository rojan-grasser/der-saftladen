<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', EnsureUserIsAdmin::class, EnsureUserIsActive::class])
    ->prefix('admin')
    ->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('AdminDashboard');
        })->name('admin.dashboard');

        // Route::get('/users', [UserController::class, 'index'])->name('admin.users');

        // Add any other admin routes here
    });

require __DIR__ . '/settings.php';
