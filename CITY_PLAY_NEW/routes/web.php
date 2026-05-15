<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationController;
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

// Routes d'invitation publiques (Entrée du jeu)
Route::get('/join/{token}', [InvitationController::class, 'join'])->name('game.join');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Routes de session de jeu
    Route::prefix('game')->name('game.')->group(function () {
        Route::post('/join/{token}', [GameSessionController::class, 'join'])->name('session.join');
        Route::get('/lobby/{session}', [GameSessionController::class, 'lobby'])->name('lobby');
        Route::post('/start/{session}', [GameSessionController::class, 'start'])->name('start');
        
        // Route temporaire pour la carte (Dev 3)
        Route::get('/map/{session}', function() { return Inertia::render('Game/Map'); })->name('map');
    });

    // Routes d'administration des invitations
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/cities/{city}/invitations/create', [InvitationController::class, 'create'])->name('invitations.create');
        Route::post('/cities/{city}/invitations', [InvitationController::class, 'store'])->name('invitations.store');
    });
});

require __DIR__.'/auth.php';
