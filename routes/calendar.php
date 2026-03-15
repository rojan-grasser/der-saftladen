<?php

use App\Http\Controllers\Calender\AppointmentController;
use App\Http\Controllers\Calender\PushSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::resource('appointments', AppointmentController::class)->except(['create', 'edit', 'show']);

    Route::post('/reminder/test-email', [PushSubscriptionController::class, 'testEmail'])->name('reminder.test-email');
});
