<?php
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home')->middleware('request-logging');

Route::get('dashboard', function () {
    return redirect()->route('appointments.index');
})->middleware(['request-logging', 'auth', 'verified', 'active'])->name('dashboard');

require __DIR__ . '/admin.php';
require __DIR__ . '/calendar.php';
require __DIR__ . '/forum.php';
require __DIR__ . '/settings.php';
