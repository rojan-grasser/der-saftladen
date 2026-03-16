<?php

use App\Http\Controllers\Calender\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::resource('appointments', AppointmentController::class)->except(['create', 'edit', 'show']);
});
