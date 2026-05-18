<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RiddleValidationController;
use App\Http\Controllers\GameSessionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Page d'accueil publique
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => Inertia::render('Welcome', [
    'canLogin'       => Route::has('login'),
    'canRegister'    => Route::has('register'),
    'laravelVersion' => Application::VERSION,
    'phpVersion'     => PHP_VERSION,
]));

/*
|--------------------------------------------------------------------------
| Routes joueur  →  préfixe /player
|--------------------------------------------------------------------------
*/
Route::prefix('player')
    ->middleware(['auth', 'verified'])
    ->name('player.')
    ->group(function () {

        // Dashboard joueur
        Route::get('/dashboard', fn () => Inertia::render('Dashboard'))
            ->name('dashboard');

        // Profil
        Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Énigmes
        Route::get('/riddles/{riddle}',              [RiddleValidationController::class, 'show'])->name('riddle.show');
        Route::post('/riddles/{riddle}/validate',    [RiddleValidationController::class, 'validate'])->name('riddle.validate');
        Route::post('/riddles/{riddle}/unlock-hint', [RiddleValidationController::class, 'unlockHint'])->name('riddle.unlock-hint');

        // Sessions de jeu
        Route::post('/game-sessions',                    [GameSessionController::class, 'store'])->name('game-sessions.store');
        Route::get('/game-sessions/{session}/summary',   [GameSessionController::class, 'summary'])->name('game-sessions.summary');
        Route::post('/game-sessions/{session}/pause',    [GameSessionController::class, 'pause'])->name('game-sessions.pause');
        Route::post('/game-sessions/{session}/resume',   [GameSessionController::class, 'resume'])->name('game-sessions.resume');
        Route::post('/game-sessions/{session}/abandon',  [GameSessionController::class, 'abandon'])->name('game-sessions.abandon');

        // Carte
        Route::get('/map', [GameSessionController::class, 'map'])->name('game.map');
    });

/*
|--------------------------------------------------------------------------
| Redirection rétro-compatible  /dashboard  →  /player/dashboard
| (évite les 404 si un lien externe ou un e-mail pointe encore vers /dashboard)
|--------------------------------------------------------------------------
*/
Route::redirect('/dashboard', '/player/dashboard')->middleware(['auth', 'verified']);

require __DIR__.'/auth.php';
