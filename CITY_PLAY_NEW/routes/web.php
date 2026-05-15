<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RiddleValidationController;
use App\Http\Controllers\GameSessionController;
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

    // Gameplay Routes
    Route::get('/riddles/{riddle}', [RiddleValidationController::class, 'show'])->name('riddle.show');
    Route::post('/riddles/{riddle}/validate', [RiddleValidationController::class, 'validate'])->name('riddle.validate');
    Route::post('/riddles/{riddle}/unlock-hint', [RiddleValidationController::class, 'unlockHint'])->name('riddle.unlock-hint');

    // Session Routes
    Route::post('/game-sessions', [GameSessionController::class, 'store'])->name('game-sessions.store');
    Route::get('/game-sessions/{session}/summary', [GameSessionController::class, 'summary'])->name('game-sessions.summary');
    Route::get('/map', [GameSessionController::class, 'map'])->name('game.map');
});

require __DIR__.'/auth.php';
