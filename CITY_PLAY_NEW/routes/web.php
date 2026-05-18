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

// Admin → Gestion du contenu (Énigmes)
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
});

require __DIR__.'/auth.php';
