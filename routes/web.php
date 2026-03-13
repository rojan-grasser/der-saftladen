<?php

use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\InstructorToProfessionController;
use App\Http\Controllers\Admin\ProfessionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Calender\AppointmentController;
use App\Http\Controllers\Forum\PostController;
use App\Http\Controllers\Forum\PostReactionController;
use App\Http\Controllers\Forum\ProfessionController as ForumProfessionController;
use App\Http\Controllers\Forum\TopicController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home')->middleware('request-logging');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['request-logging', 'auth', 'verified', 'active'])->name('dashboard');



Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::resource('appointments', AppointmentController::class)->except(['create', 'edit', 'show']);
});

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

Route::middleware(['auth', 'verified', 'active'])
    ->prefix('forum')
    ->group(function () {
        Route::get('professions', [ForumProfessionController::class, 'index'])->name('forum.professions');

        // Route::resource('profession', TopicController::class);
        Route::get('profession/{professionId}', [TopicController::class, 'index'])->name('topics.index');
        Route::post('profession/{professionId}/topics', [TopicController::class, 'store'])->name('topics.store');

        Route::middleware([])
            ->prefix('profession/{professionId}')
            ->group(function () {
                Route::get('/topics/{topicId}', [TopicController::class, 'show'])->name('topics.show');
                Route::put('/topics/{topicId}', [TopicController::class, 'update'])->name('topics.update');

                Route::middleware([])
                    ->prefix('topics/{topicId}')
                    ->group(function () {
                        Route::get('posts/{postId}/reactions', [PostReactionController::class, 'index'])->name('posts.reactions.index');
                        Route::post('posts/{postId}/reactions', [PostReactionController::class, 'store'])->name('posts.reactions.store');
                        Route::delete('posts/{postId}/reactions', [PostReactionController::class, 'destroy'])->name('posts.reactions.destroy');

                        Route::resource('posts', PostController::class);
                    });
            });
    });

require __DIR__ . '/settings.php';
