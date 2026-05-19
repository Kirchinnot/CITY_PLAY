<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\GameSession;
use App\Models\Invitation;
use App\Services\Session\GameSessionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class GameSessionController extends Controller
{
    protected $gameSessionService;

    public function __construct(GameSessionService $gameSessionService)
    {
        $this->gameSessionService = $gameSessionService;
    }

    /**
     * Affiche la carte du jeu.
     */
    public function map(Request $request)
    {
        $user = $request->user();
        
        $session = GameSession::whereHas('players', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereIn('status', ['active', 'paused'])
            ->with(['city', 'sessionPlaces.place.images'])
            ->latest()
            ->first();

        if (!$session) {
            return redirect()->route('player.dashboard')->with('error', 'Aucune partie active trouvée.');
        }

        Gate::authorize('view', $session);

        return Inertia::render('Gameplay/Map', [
            'session' => $session,
        ]);
    }

    /**
     * Démarre une nouvelle session (Quick Start).
     */
    public function store(Request $request)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'difficulty' => 'nullable|string',
            'mode' => 'nullable|string',
            'start_place_id' => 'nullable|exists:places,id',
        ]);

        $user = $request->user();
        
        // Empêcher d'avoir plusieurs sessions actives
        $activeSession = GameSession::whereHas('players', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereIn('status', ['active', 'paused', 'pending'])
            ->first();

        if ($activeSession) {
            return redirect()->route('game.lobby', $activeSession->id)
                ->with('info', 'Vous avez déjà une session en cours.');
        }

        $session = $this->gameSessionService->createDirectSession($user, $request->all());

        return redirect()->route('game.lobby', $session->id);
    }

    /**
     * Affiche le bilan de fin de partie.
     */
    public function summary(GameSession $session)
    {
        Gate::authorize('view', $session);

        $summaryData = $this->gameSessionService->getSummaryData($session);

        return Inertia::render('Gameplay/Summary', [
            'session' => $summaryData
        ]);
    }

    /**
     * Met la session en pause.
     */
    public function pause(GameSession $session)
    {
        Gate::authorize('manage', $session);

        if (!$this->gameSessionService->pauseSession($session)) {
            return response()->json(['message' => 'Impossible de mettre en pause.'], 422);
        }

        return response()->json([
            'status' => 'paused', 
            'paused_at' => $session->paused_at
        ]);
    }

    /**
     * Reprend la session.
     */
    public function resume(GameSession $session)
    {
        Gate::authorize('manage', $session);

        if (!$this->gameSessionService->resumeSession($session)) {
            return response()->json(['message' => 'Impossible de reprendre.'], 422);
        }

        return response()->json([
            'status' => 'active', 
            'total_pause_seconds' => $session->total_pause_seconds
        ]);
    }

    /**
     * Abandonne la session.
     */
    public function abandon(GameSession $session)
    {
        Gate::authorize('manage', $session);

        if (!$this->gameSessionService->abandonSession($session)) {
            return response()->json(['message' => 'Impossible d\'abandonner.'], 422);
        }

        return response()->json(['status' => 'abandoned']);
    }

    /**
     * Rejoindre via token.
     */
    public function join(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();
        $user = $request->user();

        $session = $this->gameSessionService->joinOrCreateSession($invitation, $user);

        return redirect()->route('game.lobby', $session->id);
    }

    /**
     * Salon d'attente.
     */
    public function lobby(GameSession $session)
    {
        Gate::authorize('view', $session);

        $session->load(['city', 'invitation', 'players']);

        return Inertia::render('Game/Lobby', [
            'session' => $session,
            'currentUser' => auth()->user(),
            'invitationUrl' => route('game.join', ['token' => $session->invitation->token]),
        ]);
    }

    /**
     * Lancement de la partie.
     */
    public function start(Request $request, GameSession $session)
    {
        Gate::authorize('manage', $session);

        // Mettre à jour les paramètres de la session choisis dans le Lobby
        $session->update($request->only([
            'difficulty', 
            'mode', 
            'locomotion', 
            'available_minutes',
            'team_size'
        ]));

        if ($this->gameSessionService->startSession($session, $request->start_place_id)) {
            return redirect()->route('player.game.map');
        }

        return back()->with('error', 'Échec du lancement de la partie.');
    }
}
