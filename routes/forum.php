<?php

use App\Http\Controllers\Forum\PostController;
use App\Http\Controllers\Forum\PostReactionController;
use App\Http\Controllers\Forum\ProfessionController as ForumProfessionController;
use App\Http\Controllers\Forum\TopicController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'active'])
    ->prefix('forum')
    ->group(function () {
        Route::get('professions', [ForumProfessionController::class, 'index'])->name('forum.professions');

        Route::middleware(['instructor-has-access'])
            ->prefix('profession/{professionId}')
            ->group(function () {
                Route::get('', [TopicController::class, 'index'])->name('topics.index');
                Route::post('/topics', [TopicController::class, 'store'])->name('topics.store');

                Route::get('/topics/{topicId}', [TopicController::class, 'show'])->name('topics.show');
                Route::put('/topics/{topicId}', [TopicController::class, 'update'])->name('topics.update');

                Route::middleware([])
                    ->prefix('topics/{topicId}')
                    ->group(function () {
                        Route::put('pin', [TopicController::class, 'togglePin'])->name('topics.pin-toggle');

                        Route::get('posts/{postId}/reactions', [PostReactionController::class, 'index'])->name('posts.reactions.index');
                        Route::post('posts/{postId}/reactions', [PostReactionController::class, 'store'])->name('posts.reactions.store');
                        Route::delete('posts/{postId}/reactions', [PostReactionController::class, 'destroy'])->name('posts.reactions.destroy');

                        Route::resource('posts', PostController::class);
                    });
            });
    });
