<?php

use App\Http\Controllers\Admin\ProfessionalAreaController;
use App\Http\Controllers\Admin\UserController;
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
})->middleware(['auth', 'verified', 'active'])->name('dashboard');

Route::middleware(['auth', 'verified', 'active', 'admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('AdminDashboard');
        })->name('admin.dashboard');

        Route::get('/users', [UserController::class, 'index'])->name('admin.users');
        Route::put('/users/{id}', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::post('/professional-area', [ProfessionalAreaController::class, 'index'])->name('admin.professional-area.create');
        Route::put('/professional-area/{id}', [ProfessionalAreaController::class, 'update'])->name('admin.professional-area.update');

        // Add any other admin routes here
    });

require __DIR__ . '/settings.php';
