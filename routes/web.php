<?php

use App\Http\Controllers\Admin\InstructorToProfessionalAreaController;
use App\Http\Controllers\Admin\InstructorController;
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
            return Inertia::render('admin/AdminDashboard');
        })->name('admin.dashboard');

        Route::get('/users', [UserController::class, 'index'])->name('admin.users');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
        Route::get('/instructors', [InstructorController::class, 'index'])->name('admin.instructors');
        Route::get('/professional-areas', [ProfessionalAreaController::class, 'get'])->name('admin.professional-area.get');
        Route::post('/professional-area', [ProfessionalAreaController::class, 'index'])->name('admin.professional-area.create');
        Route::put('/professional-area/{id}', [ProfessionalAreaController::class, 'update'])->name('admin.professional-area.update');
        Route::delete('/professional-area/{id}', [ProfessionalAreaController::class, 'destroy'])->name('admin.professional-area.destroy');
        Route::get('/professional-area/{id}/instructors', [ProfessionalAreaController::class, 'getInstructors'])->name('admin.professional-area.instructors');

        Route::post('/instructors-to-area/{instructorId}/{areaId}', [InstructorToProfessionalAreaController::class, 'index'])->name('admin.instructors-to-area.add');
        Route::delete('/instructors-to-area/{instructorId}/{areaId}', [InstructorToProfessionalAreaController::class, 'destroy'])->name('admin.instructors-to-area.destroy');

        // Add any other admin routes here
    });

require __DIR__ . '/settings.php';
