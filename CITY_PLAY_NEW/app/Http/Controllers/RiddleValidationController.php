<?php

namespace App\Http\Controllers;

use App\Models\Riddle;
use App\Models\GameSession;
use App\Models\Hint;
use App\Models\HintUsage;
use App\Services\Session\GameSessionService;
use App\Traits\GameplayLogic;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class RiddleValidationController extends Controller
{
    use GameplayLogic;

    protected $gameSessionService;

    public function __construct(GameSessionService $gameSessionService)
    {
        $this->gameSessionService = $gameSessionService;
    }

    /**
     * Affiche l'énigme à résoudre.
     */
    public function show(Riddle $riddle)
    {
        Gate::authorize('solve', $riddle);

        $user = auth()->user();
        $session = GameSession::whereHas('players', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('status', 'active')
            ->firstOrFail();

        $riddle->load(['place', 'hints', 'images']);

        $unlockedHintIds = HintUsage::where('game_session_id', $session->id)
            ->where('riddle_id', $riddle->id)
            ->pluck('hint_id')
            ->toArray();

        return Inertia::render('Gameplay/RiddleValidation', [
            'riddle' => $riddle,
            'session' => $session,
            'unlockedHintIds' => $unlockedHintIds,
        ]);
    }

    /**
     * Valide la réponse à l'énigme et la position GPS.
     */
    public function validate(Request $request, Riddle $riddle)
    {
        Gate::authorize('solve', $riddle);

        $user = $request->user();
        $session = GameSession::whereHas('players', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('status', 'active')
            ->firstOrFail();

        // 1. Validation de la position GPS
        if (!$request->lat || !$request->lng) {
            return response()->json(['message' => 'Coordonnées GPS manquantes.'], 422);
        }

        $distance = $this->calculateDistance($request->lat, $request->lng, $riddle->place->lat, $riddle->place->lng);
        $radius = $riddle->place->validation_radius ?? 30;

        if ($distance > $radius) {
            return response()->json([
                'message' => 'Vous n\'êtes pas encore assez proche du lieu !',
                'distance' => round($distance),
                'required_radius' => $radius
            ], 422);
        }

        // 2. Validation de la réponse
        if ($riddle->answer) {
            if (!$request->answer || !$this->compareText($request->answer, $riddle->answer)) {
                return response()->json([
                    'message' => 'Mauvaise réponse !',
                    'error_type' => 'wrong_answer',
                    'can_skip' => true,
                ], 422);
            }
        }

        // 3. Enregistrement via le Service (le score est calculé automatiquement dans le service)
        $this->gameSessionService->resolveRiddle($session, $riddle, $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Félicitations ! Vous avez résolu l\'énigme.',
        ]);
    }

    /**
     * Débloque un indice.
     */
    public function unlockHint(Request $request, Riddle $riddle)
    {
        Gate::authorize('solve', $riddle);

        $request->validate(['hint_id' => 'required|exists:hints,id']);
        
        $user = $request->user();
        $session = GameSession::whereHas('players', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('status', 'active')
            ->firstOrFail();

        HintUsage::firstOrCreate([
            'game_session_id' => $session->id,
            'hint_id' => $request->hint_id,
        ], [
            'user_id' => $user->id,
            'riddle_id' => $riddle->id,
        ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Passe l'énigme.
     */
    public function skip(Riddle $riddle)
    {
        Gate::authorize('solve', $riddle);

        $user = auth()->user();
        $session = GameSession::whereHas('players', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('status', 'active')
            ->firstOrFail();

        $this->gameSessionService->resolveRiddle($session, $riddle, $user, 0);

        return redirect()->route('player.game.map')->with('info', 'Énigme passée.');
    }

    /**
     * Révèle la solution.
     */
    public function revealSolution(Riddle $riddle)
    {
        Gate::authorize('solve', $riddle);

        return response()->json([
            'solution' => $riddle->answer,
        ]);
    }
}
