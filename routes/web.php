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

Route::middleware([])
    ->prefix('admin')
    ->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('admin/AdminDashboard');
        })->name('admin.dashboard');

        Route::get('/users', [UserController::class, 'index'])->name('admin.users');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
        Route::post('/professional-area', [ProfessionalAreaController::class, 'index'])->name('admin.professional-area.create');
        Route::put('/professional-area/{id}', [ProfessionalAreaController::class, 'update'])->name('admin.professional-area.update');
        Route::delete('/professional-area/{id}', [ProfessionalAreaController::class, 'destroy'])->name('admin.professional-area.destroy');

        // Add any other admin routes here
    });

require __DIR__ . '/settings.php';
