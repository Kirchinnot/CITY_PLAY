<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\SessionPlace;
use App\Models\Invitation;
use App\Services\Session\GameSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GameSessionController extends Controller
{
    protected $gameSessionService;

    public function __construct(GameSessionService $gameSessionService)
    {
        $this->gameSessionService = $gameSessionService;
    }

    /**
     * Affiche la carte du jeu avec la position du joueur et les POI.
     */
    public function map(Request $request)
    {
        $user = $request->user();
        
        $session = GameSession::whereHas('gamePlayers', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereIn('status', ['active', 'paused'])
            ->with(['city', 'sessionPlaces.place.images'])
            ->latest()
            ->first();

        if (!$session) {
            return redirect()->route('player.dashboard')->with('error', 'Aucune partie active trouvée.');
        }

        return Inertia::render('Gameplay/Map', [
            'session' => $session,
        ]);
    }

    /**
     * Démarre une nouvelle session de jeu pour une ville donnée (Quick Start).
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
        
        // Vérifier si une session active existe déjà
        $activeSession = GameSession::whereHas('gamePlayers', function($query) use ($user) {
                $query->where('user_id', $user->id)->where('is_active', true);
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
     * Affiche le bilan d'une session terminée.
     */
    public function summary(GameSession $session)
    {
        $session->load(['city', 'scores.riddle.place.images', 'achievements', 'sessionPlaces.place.images']);

        // Calcul du temps total (en secondes)
        $totalTime = 0;
        if ($session->started_at && $session->completed_at) {
            $totalTime = $session->started_at->diffInSeconds($session->completed_at);
        }

        // Trouver les lieux non résolus
        $unsolvedPlaces = $session->sessionPlaces()
            ->where('is_completed', false)
            ->with('place.images')
            ->get()
            ->map(fn($sp) => $sp->place);
        
        return Inertia::render('Gameplay/Summary', [
            'session' => [
                'id' => $session->id,
                'city_name' => $session->city?->name ?? 'Ville inconnue',
                'outro_config' => $session->city?->outro_config,
                'total_score' => $session->scores->sum('points_earned'),
                'total_time' => $totalTime,
                'solved_places' => $session->solved_places,
                'total_places' => $session->total_places,
                'difficulty' => $session->difficulty,
                'mode' => $session->mode,
                'unsolved_places' => $unsolvedPlaces,
                'scores' => $session->scores,
                'achievements' => $session->achievements,
            ]
        ]);
    }

    /**
     * Met la session en pause.
     */
    public function pause(Request $request, GameSession $session)
    {
        $user = $request->user();

        if ($session->host_user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if (!$this->gameSessionService->pauseSession($session)) {
            return response()->json(['message' => 'Impossible de mettre en pause.'], 422);
        }

        return response()->json(['status' => 'paused', 'paused_at' => $session->paused_at]);
    }

    /**
     * Reprend une session en pause.
     */
    public function resume(Request $request, GameSession $session)
    {
        $user = $request->user();

        if ($session->host_user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if (!$this->gameSessionService->resumeSession($session)) {
            return response()->json(['message' => 'Impossible de reprendre.'], 422);
        }

        return response()->json(['status' => 'active', 'total_pause_seconds' => $session->total_pause_seconds]);
    }

    /**
     * Abandonne une session.
     */
    public function abandon(Request $request, GameSession $session)
    {
        $user = $request->user();

        if ($session->host_user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if (!$this->gameSessionService->abandonSession($session)) {
            return response()->json(['message' => 'Impossible d\'abandonner.'], 422);
        }

        return response()->json(['status' => 'abandoned']);
    }

    /**
     * Rejoindre une session de jeu via un lien d'invitation (token).
     */
    public function join(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();
        $user = $request->user();

        $session = $this->gameSessionService->joinOrCreateSession($invitation, $user);

        return redirect()->route('game.lobby', $session->id);
    }

    /**
     * Affiche le salon d'attente (Lobby).
     */
    public function lobby(Request $request, GameSession $session)
    {
        $session->load(['city', 'invitation', 'players']);

        return Inertia::render('Game/Lobby', [
            'session' => $session,
            'currentUser' => $request->user(),
            'invitationUrl' => route('game.join', ['token' => $session->invitation->token]),
        ]);
    }

    /**
     * Le chef de clan lance la partie depuis le lobby.
     */
    public function start(Request $request, GameSession $session)
    {
        $user = $request->user();

        if ($session->host_user_id !== $user->id) {
            return redirect()->back()->with('error', 'Seul le chef de clan peut démarrer la partie.');
        }

        // Mise à jour des paramètres si fournis (depuis le formulaire du lobby)
        $this->gameSessionService->updateSettings($session, $request->only([
            'difficulty', 'locomotion', 'available_minutes', 'mode', 'team_size'
        ]));

        if (!$this->gameSessionService->startSession($session, $session->start_place_id)) {
            return redirect()->back()->with('error', 'Impossible de démarrer la partie.');
        }

        return redirect()->route('player.game.map')->with('success', 'L\'aventure commence !');
    }

    /**
     * Sélectionne un lieu sur la carte (pour le mode mercenaire ou choix libre).
     */
    public function selectPlace(Request $request, GameSession $session)
    {
        $request->validate(['place_id' => 'required|exists:places,id']);

        $sessionPlace = $session->sessionPlaces()
            ->where('place_id', $request->place_id)
            ->firstOrFail();

        if ($sessionPlace->is_completed) {
            return response()->json(['message' => 'Lieu déjà complété.'], 422);
        }

        $session->update(['current_place_index' => $sessionPlace->order_index]);

        return response()->json(['status' => 'success']);
    }
}
