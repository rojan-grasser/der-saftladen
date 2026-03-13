<?php

use App\Http\Controllers\Calender\AppointmentController;
use App\Http\Controllers\Calender\PushSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::resource('appointments', AppointmentController::class)->except(['create', 'edit', 'show']);

    Route::get('/push/vapid-key', [PushSubscriptionController::class, 'vapidPublicKey'])->name('push.vapid-key');
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');
});
