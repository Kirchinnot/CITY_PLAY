<?php

namespace App\Http\Controllers;

use App\Models\GameSession;
use App\Models\HintUsage;
use App\Models\Riddle;
use App\Models\RiddleAttempt;
use App\Models\User;
use App\Services\Session\GameSessionService;
use App\Traits\GameplayLogic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class RiddleValidationController extends Controller
{
    use GameplayLogic;

    public function __construct(
        protected GameSessionService $gameSessionService
    ) {}

    /**
     * Affiche l'énigme à résoudre.
     */
    public function show(Riddle $riddle): Response
    {
        Gate::authorize('solve', $riddle);

        $user = auth()->user();
        $session = $this->getActiveSession($user);

        $riddle->load(['place.images', 'hints', 'images']);

        $unlockedHintIds = HintUsage::where('game_session_id', $session->id)
            ->where('riddle_id', $riddle->id)
            ->pluck('hint_id')
            ->toArray();

        $attempt = $this->getRiddleAttempt($session, $riddle, $user);

        return Inertia::render('Gameplay/RiddleValidation', [
            'riddle' => $riddle,
            'session' => $session,
            'unlockedHintIds' => $unlockedHintIds,
            'qcmValidated' => (bool) $attempt?->isQcmValidated(),
            'selectedAnswer' => $attempt?->selected_answer,
        ]);
    }

    /**
     * Étape 1 — Confirme la réponse (QCM ou texte libre), sans GPS.
     */
    public function submitAnswer(Request $request, Riddle $riddle): JsonResponse
    {
        Gate::authorize('solve', $riddle);

        $request->validate(['answer' => 'required|string|max:255']);

        $user = $request->user();
        $session = $this->getActiveSession($user);

        $attempt = $this->getRiddleAttempt($session, $riddle, $user);
        if ($attempt?->isQcmValidated()) {
            return response()->json([
                'status' => 'success',
                'step' => 'onsite',
                'message' => 'Réponse déjà confirmée. Rendez-vous sur place pour valider.',
                'selected_answer' => $attempt->selected_answer,
            ]);
        }

        if (!$this->isAnswerCorrect($riddle, $request->answer)) {
            return response()->json([
                'message' => 'Mauvaise réponse !',
                'error_type' => 'wrong_answer',
                'can_skip' => true,
            ], 422);
        }

        RiddleAttempt::updateOrCreate(
            [
                'game_session_id' => $session->id,
                'riddle_id' => $riddle->id,
                'user_id' => $user->id,
            ],
            [
                'selected_answer' => $request->answer,
                'qcm_validated_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'step' => 'onsite',
            'message' => 'Bonne réponse ! Rendez-vous sur place pour valider votre présence.',
            'selected_answer' => $request->answer,
        ]);
    }

    /**
     * Étape 2 — Valide la présence GPS sur le lieu (réponse déjà confirmée).
     */
    public function validatePresence(Request $request, Riddle $riddle): JsonResponse
    {
        Gate::authorize('solve', $riddle);

        $user = $request->user();
        $session = $this->getActiveSession($user);

        $attempt = $this->getRiddleAttempt($session, $riddle, $user);
        if (!$attempt?->isQcmValidated()) {
            return response()->json([
                'message' => 'Confirmez d\'abord votre réponse avant de valider sur place.',
                'step' => 'qcm',
            ], 422);
        }

        if (!$request->lat || !$request->lng) {
            return response()->json(['message' => 'Coordonnées GPS manquantes.'], 422);
        }

        $distance = $this->calculateDistance(
            (float) $request->lat,
            (float) $request->lng,
            (float) $riddle->place->lat,
            (float) $riddle->place->lng
        );
        $radius = $riddle->place->validation_radius ?? 30;

        if ($distance > $radius) {
            return response()->json([
                'message' => 'Vous n\'êtes pas encore assez proche du lieu !',
                'distance' => round($distance),
                'required_radius' => $radius,
            ], 422);
        }

        $this->gameSessionService->resolveRiddle($session, $riddle, $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Félicitations ! Vous avez résolu l\'énigme.',
        ]);
    }

    /**
     * @deprecated Utiliser submitAnswer + validatePresence
     */
    public function validate(Request $request, Riddle $riddle): JsonResponse
    {
        Gate::authorize('solve', $riddle);

        $user = $request->user();
        $session = $this->getActiveSession($user);

        $attempt = $this->getRiddleAttempt($session, $riddle, $user);

        if (!$attempt?->isQcmValidated()) {
            if (!$request->answer || !$this->isAnswerCorrect($riddle, $request->answer)) {
                return response()->json([
                    'message' => 'Mauvaise réponse !',
                    'error_type' => 'wrong_answer',
                    'can_skip' => true,
                ], 422);
            }

            RiddleAttempt::updateOrCreate(
                [
                    'game_session_id' => $session->id,
                    'riddle_id' => $riddle->id,
                    'user_id' => $user->id,
                ],
                [
                    'selected_answer' => $request->answer,
                    'qcm_validated_at' => now(),
                ]
            );
        }

        return $this->validatePresence($request, $riddle);
    }

    /**
     * Débloque un indice.
     */
    public function unlockHint(Request $request, Riddle $riddle): JsonResponse
    {
        Gate::authorize('solve', $riddle);

        $request->validate(['hint_id' => 'required|exists:hints,id']);

        $user = $request->user();
        $session = $this->getActiveSession($user);

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
        $session = $this->getActiveSession($user);

        $this->gameSessionService->resolveRiddle($session, $riddle, $user, 0);

        return redirect()->route('player.game.map')->with('info', 'Énigme passée.');
    }

    /**
     * Révèle la solution.
     */
    public function revealSolution(Riddle $riddle): JsonResponse
    {
        Gate::authorize('solve', $riddle);

        return response()->json([
            'solution' => $riddle->answer,
        ]);
    }

    protected function getActiveSession(User $user): GameSession
    {
        $session = GameSession::whereHas('players', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->whereIn('status', ['active', 'paused'])
            ->firstOrFail();

        $this->gameSessionService->syncTimerState($session);
        $session->refresh();

        if ($session->isFinished()) {
            abort(422, 'Le temps de jeu est écoulé. Consultez votre bilan.');
        }

        if ($session->isPaused()) {
            abort(422, 'La partie est en pause. Reprenez depuis la carte.');
        }

        return $session;
    }

    protected function getRiddleAttempt(GameSession $session, Riddle $riddle, User $user): ?RiddleAttempt
    {
        return RiddleAttempt::where('game_session_id', $session->id)
            ->where('riddle_id', $riddle->id)
            ->where('user_id', $user->id)
            ->first();
    }

    protected function isAnswerCorrect(Riddle $riddle, string $answer): bool
    {
        if (!$riddle->answer) {
            return true;
        }

        $normalize = fn (string $value) => mb_strtolower(trim($value));

        if ($riddle->options && count($riddle->options) > 0) {
            $userNorm = $normalize($answer);
            $correctNorm = $normalize($riddle->answer);

            if ($userNorm === $correctNorm) {
                return true;
            }

            // Vérifie que le choix sélectionné correspond bien à l'une des options affichées
            foreach ($riddle->options as $option) {
                if ($normalize((string) $option) === $userNorm) {
                    return $normalize((string) $option) === $correctNorm;
                }
            }

            return false;
        }

        return $this->compareText($answer, $riddle->answer);
    }
}
