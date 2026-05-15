<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin → Gestion du contenu (Énigmes)
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/places', [\App\Http\Controllers\Admin\PlaceController::class, 'index'])->name('places.index');
    Route::post('/places', [\App\Http\Controllers\Admin\PlaceController::class, 'store'])->name('places.store');
    Route::get('/places/{place}/riddles', [\App\Http\Controllers\Admin\RiddleController::class, 'index'])->name('riddles.index');
    Route::post('/places/{place}/riddles', [\App\Http\Controllers\Admin\RiddleController::class, 'store'])->name('riddles.store');
});

require __DIR__.'/auth.php';
