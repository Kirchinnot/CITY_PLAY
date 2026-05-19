<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationController;
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
| Routes d'invitation publiques (Entrée du jeu)
|--------------------------------------------------------------------------
*/
Route::get('/join/{token}', [InvitationController::class, 'join'])->name('game.join');

/*
|--------------------------------------------------------------------------
| Routes Authentifiées de Session de Jeu & Administration des invitations
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Profil utilisateur générique
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/logout-delete', [ProfileController::class, 'logoutAndDelete'])->name('profile.logout-delete');

    // Sessions de jeu
    Route::prefix('game')->name('game.')->group(function () {
        Route::post('/join/{token}', [GameSessionController::class, 'join'])->name('session.join');
        Route::get('/lobby/{session}', [GameSessionController::class, 'lobby'])->name('lobby');
        Route::post('/start/{session}', [GameSessionController::class, 'start'])->name('start');
        
        // Route temporaire pour la carte (Dev 3)
        Route::get('/map/{session}', function() { return Inertia::render('Game/Map'); })->name('map');
    });

    // Administration des invitations de jeu
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/cities/{city}/invitations/create', [InvitationController::class, 'create'])->name('invitations.create');
        Route::post('/cities/{city}/invitations', [InvitationController::class, 'store'])->name('invitations.store');
    });
});

/*
|--------------------------------------------------------------------------
| Routes joueur  →  préfixe /player (Dashboard, Énigmes et Gameplay en cours)
|--------------------------------------------------------------------------
*/
Route::prefix('player')
    ->middleware(['auth', 'verified'])
    ->name('player.')
    ->group(function () {
        // Dashboard joueur
        Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');

        // Profil joueur
        Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Énigmes
        Route::get('/riddles/{riddle}',                    [RiddleValidationController::class, 'show'])->name('riddle.show');
        Route::post('/riddles/{riddle}/submit-answer',     [RiddleValidationController::class, 'submitAnswer'])->name('riddle.submit-answer');
        Route::post('/riddles/{riddle}/validate-presence', [RiddleValidationController::class, 'validatePresence'])->middleware('verify.speed')->name('riddle.validate-presence');
        Route::post('/riddles/{riddle}/validate',          [RiddleValidationController::class, 'validate'])->middleware('verify.speed')->name('riddle.validate');
        Route::post('/riddles/{riddle}/unlock-hint',       [RiddleValidationController::class, 'unlockHint'])->name('riddle.unlock-hint');
        Route::post('/riddles/{riddle}/skip',        [RiddleValidationController::class, 'skip'])->name('riddle.skip');
        Route::post('/riddles/{riddle}/reveal-solution', [RiddleValidationController::class, 'revealSolution'])->name('riddle.reveal-solution');

        // Sessions de jeu gameplay
        Route::post('/game-sessions',                    [GameSessionController::class, 'store'])->name('game-sessions.store');
        Route::get('/game-sessions/{session}/summary',   [GameSessionController::class, 'summary'])->name('game-sessions.summary');
        Route::post('/game-sessions/{session}/sync',    [GameSessionController::class, 'sync'])->name('game-sessions.sync');
        Route::post('/game-sessions/{session}/pause',    [GameSessionController::class, 'pause'])->name('game-sessions.pause');
        Route::post('/game-sessions/{session}/resume',   [GameSessionController::class, 'resume'])->name('game-sessions.resume');
        Route::post('/game-sessions/{session}/abandon',  [GameSessionController::class, 'abandon'])->name('game-sessions.abandon');
        Route::post('/game-sessions/{session}/select-place', [GameSessionController::class, 'selectPlace'])->name('game-sessions.select-place');

        // Carte
        Route::get('/map', [GameSessionController::class, 'map'])->name('game.map');
    });

/*
|--------------------------------------------------------------------------
| Admin → Gestion du contenu (Villes, Lieux, Énigmes)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Villes / Parcours
    Route::get('/cities', [\App\Http\Controllers\Admin\CityController::class, 'index'])->name('cities.index');
    Route::post('/cities', [\App\Http\Controllers\Admin\CityController::class, 'store'])->name('cities.store');
    Route::put('/cities/{city}', [\App\Http\Controllers\Admin\CityController::class, 'update'])->name('cities.update');
    Route::delete('/cities/{city}', [\App\Http\Controllers\Admin\CityController::class, 'destroy'])->name('cities.destroy');
    Route::post('/cities/{city}/publish', [\App\Http\Controllers\Admin\CityController::class, 'publish'])->name('cities.publish');
    Route::post('/cities/{city}/unpublish', [\App\Http\Controllers\Admin\CityController::class, 'unpublish'])->name('cities.unpublish');

    // Lieux
    Route::get('/places', [\App\Http\Controllers\Admin\PlaceController::class, 'index'])->name('places.index');
    Route::post('/places', [\App\Http\Controllers\Admin\PlaceController::class, 'store'])->name('places.store');
    Route::delete('/places/{place}', [\App\Http\Controllers\Admin\PlaceController::class, 'destroy'])->name('places.destroy');
    Route::delete('/place-images/{image}', [\App\Http\Controllers\Admin\PlaceController::class, 'destroyImage'])->name('place-images.destroy');

    // Énigmes
    Route::get('/places/{place}/riddles', [\App\Http\Controllers\Admin\RiddleController::class, 'index'])->name('riddles.index');
    Route::post('/places/{place}/riddles', [\App\Http\Controllers\Admin\RiddleController::class, 'store'])->name('riddles.store');

    // Joueurs & Équipes
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
});

/*
|--------------------------------------------------------------------------
| Redirection rétro-compatible  /dashboard  →  /player/dashboard
|--------------------------------------------------------------------------
*/
Route::redirect('/dashboard', '/player/dashboard')->middleware(['auth', 'verified']);
Route::redirect('/admin/dashboard', '/player/dashboard')->middleware(['auth', 'verified']);
Route::redirect('/admin', '/player/dashboard')->middleware(['auth', 'verified']);

require __DIR__.'/auth.php';
