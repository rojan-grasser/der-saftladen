<?php

use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\InstructorToProfessionController;
use App\Http\Controllers\Admin\ProfessionController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['request-logging', 'auth', 'verified', 'active', 'role:admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('admin/AdminDashboard');
        })->name('admin.dashboard');

        Route::get('/users', [UserController::class, 'index'])->name('admin.users');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::get('/instructors', [InstructorController::class, 'index'])->name('admin.instructors');
        Route::get('/professions', [ProfessionController::class, 'index'])->name('admin.profession');
        Route::post('/profession', [ProfessionController::class, 'store'])->name('admin.profession.store');
        Route::put('/profession/{id}', [ProfessionController::class, 'update'])->name('admin.profession.update');
        Route::delete('/profession/{id}', [ProfessionController::class, 'destroy'])->name('admin.profession.destroy');
        Route::get('/profession/{id}/instructors', [ProfessionController::class, 'getInstructors'])->name('admin.profession.instructors');

        Route::post('/instructors-to-profession/{instructorId}/{professionId}', [InstructorToProfessionController::class, 'index'])->name('admin.instructors-to-profession.add');
        Route::delete('/instructors-to-profession/{instructorId}/{professionId}', [InstructorToProfessionController::class, 'destroy'])->name('admin.instructors-to-profession.destroy');

        // Add any other admin routes here
    });
