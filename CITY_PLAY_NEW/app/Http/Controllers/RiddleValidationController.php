<?php

namespace App\Http\Controllers;

use App\Events\RiddleResolvedEvent;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\HintUsage;
use App\Models\PositionLog;
use App\Models\Riddle;
use App\Models\RiddleLock;
use App\Models\Score;
use App\Models\SessionPlace;
use App\Traits\GameplayLogic;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RiddleValidationController extends Controller
{
    use GameplayLogic;

    /**
     * Affiche l'interface de l'énigme.
     */
    public function show(Request $request, Riddle $riddle): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        
        $session = GameSession::whereHas('gamePlayers', function($query) use ($user) {
                $query->where('user_id', $user->id)->where('is_active', true);
            })
            ->where('status', 'active')
            ->firstOrFail();

        // Vérifier que l'énigme appartient bien à un lieu de cette session
        $hasPlace = $session->sessionPlaces()->where('place_id', $riddle->place_id)->exists();

        if (!$hasPlace) {
            return redirect()->route('player.dashboard')->with('error', 'Cette énigme ne correspond pas à votre parcours.');
        }

        $unlockedHintIds = HintUsage::where('game_session_id', $session->id)
            ->where('riddle_id', $riddle->id)
            ->where('user_id', $user->id)
            ->pluck('hint_id')
            ->toArray();

        // Calcul du score total pour l'affichage
        $session->total_score = $session->scores()->sum('points_earned');

        return \Inertia\Inertia::render('Gameplay/RiddleValidation', [
            'riddle' => $riddle->load(['place', 'hints', 'images']),
            'session' => $session,
            'unlockedHintIds' => $unlockedHintIds,
        ]);
    }

    /**
     * Débloque un indice pour une énigme.
     */
    public function unlockHint(Request $request, Riddle $riddle): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|exists:game_sessions,id',
            'hint_id' => 'required|exists:hints,id',
        ]);

        $user = $request->user();
        $session = GameSession::findOrFail($request->session_id);

        // Vérifier si déjà débloqué
        $exists = HintUsage::where('game_session_id', $session->id)
            ->where('hint_id', $request->hint_id)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Indice déjà débloqué.'], 200);
        }

        // Calculer les points à déduire (par exemple 30 points par indice)
        $pointsToDeduct = 30; // À dynamiser si besoin

        HintUsage::create([
            'user_id' => $user->id,
            'game_session_id' => $session->id,
            'riddle_id' => $riddle->id,
            'hint_id' => $request->hint_id,
            'points_deducted' => $pointsToDeduct,
        ]);

        return response()->json([
            'message' => 'Indice débloqué !',
            'hint_id' => $request->hint_id
        ]);
    }

    /**
     * Valide une tentative de résolution d'énigme.
     */
    public function validate(Request $request, Riddle $riddle): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|exists:game_sessions,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'answer' => 'nullable|string',
        ]);

        $user = $request->user();
        $session = GameSession::findOrFail($request->session_id);

        // 1. Vérification de la session active
        if (!$session->isActive()) {
            return response()->json(['message' => 'La session n\'est pas active.'], 403);
        }

        // 2. Anti-Triche, Télémétrie GPS et Logs : Gérés de manière centralisée et réutilisable par le middleware VerifyPlayerSpeed.

        // 3. Validation GPS — Place utilise lat/lng (pas latitude/longitude)
        $place = $riddle->place;
        $distanceToTarget = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            (float) $place->lat,
            (float) $place->lng
        );

        $validationRadius = $place->validation_radius ?: 30;
        $gpsValid = $distanceToTarget <= $validationRadius;

        if (!$gpsValid) {
            return response()->json([
                'message' => 'Vous êtes trop loin du lieu cible.',
                'distance' => round($distanceToTarget),
                'radius' => $validationRadius
            ], 403);
        }

        // 4. Validation Texte (si réponse attendue)
        if ($riddle->answer) {
            if (!$request->answer || !$this->compareText($request->answer, $riddle->answer)) {
                return response()->json(['message' => 'Réponse incorrecte.'], 422);
            }
        }

        // 5. Mode Mercenaire : Verrouillage
        if ($session->mode === 'mercenaire') {
            $lock = RiddleLock::where('game_session_id', $session->id)
                ->where('riddle_id', $riddle->id)
                ->first();

            if ($lock && $lock->user_id !== $user->id) {
                return response()->json(['message' => 'Cette énigme a déjà été verrouillée par un autre joueur.'], 403);
            }

            if (!$lock) {
                RiddleLock::create([
                    'game_session_id' => $session->id,
                    'riddle_id' => $riddle->id,
                    'user_id' => $user->id,
                    'locked_at' => now(),
                    'expires_at' => now()->addMinutes(30),
                ]);
            }
        }

        // 6. Vérification que le lieu n'est pas déjà validé
        $sessionPlace = SessionPlace::where('game_session_id', $session->id)
            ->where('place_id', $riddle->place_id)
            ->first();

        if ($sessionPlace && $sessionPlace->is_completed) {
            return response()->json(['message' => 'Ce lieu a déjà été validé.'], 403);
        }

        // Calcul du temps depuis le début de la session (SessionPlace n'a pas de created_at)
        $timeTakenSeconds = $session->started_at
            ? Carbon::parse($session->started_at)->diffInSeconds(now())
            : 0;

        $hintsUsedCount = HintUsage::where('game_session_id', $session->id)
            ->where('riddle_id', $riddle->id)
            ->where('user_id', $user->id)
            ->count();

        $scoreData = $this->calculateRiddleScore(
            $riddle,
            $timeTakenSeconds,
            $hintsUsedCount,
            $gpsValid,
            $distanceToTarget
        );

        // 7. Persistance
        return DB::transaction(function () use ($session, $user, $riddle, $scoreData, $hintsUsedCount, $timeTakenSeconds, $distanceToTarget) {
            $score = Score::create([
                'game_session_id' => $session->id,
                'user_id' => $session->mode === 'mercenaire' ? $user->id : null,
                'riddle_id' => $riddle->id,
                'points_earned' => $scoreData['total'],
                'points_speed' => $scoreData['speed'],
                'points_distance' => $scoreData['distance'],
                'points_hints_bonus' => $scoreData['hints_bonus'],
                'points_difficulty' => $scoreData['difficulty'],
                'hints_used' => $hintsUsedCount,
                'time_taken_seconds' => $timeTakenSeconds,
                'distance_m' => $distanceToTarget,
                'resolved_at' => now(),
            ]);

            // Récupérer tous les IDs des énigmes de ce lieu filtrées par la difficulté de la session
            $placeRiddleIds = \App\Models\Riddle::where('place_id', $riddle->place_id)
                ->where('difficulty', $session->difficulty)
                ->pluck('id')
                ->toArray();

            // Récupérer les IDs des énigmes déjà résolues dans cette session (incluant celle qu'on vient d'enregistrer)
            $solvedRiddleIds = Score::where('game_session_id', $session->id)
                ->pluck('riddle_id')
                ->toArray();
            
            // Si toutes les énigmes de ce lieu ont été résolues
            $allPlaceRiddlesSolved = empty(array_diff($placeRiddleIds, $solvedRiddleIds));

            if ($allPlaceRiddlesSolved) {
                // Mise à jour de SessionPlace comme complétée
                SessionPlace::where('game_session_id', $session->id)
                    ->where('place_id', $riddle->place_id)
                    ->update([
                        'is_completed' => true,
                        'completed_at' => now(),
                    ]);

                // On n'incrémente solved_places que lorsque le lieu entier est résolu
                $session->increment('solved_places');
            }

            $session->refresh();

            $isFinished = false;
            if ($session->solved_places >= $session->total_places) {
                $session->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                $isFinished = true;
            } else {
                // Si le lieu actuel est complété, on passe à l'index du lieu suivant
                if ($allPlaceRiddlesSolved) {
                    $session->increment('current_place_index');
                }
            }

            // Trouver la prochaine énigme à résoudre dans toute la session
            $nextRiddleId = null;
            if (!$isFinished) {
                // Recharger les énigmes résolues
                $solvedRiddleIds = Score::where('game_session_id', $session->id)
                    ->pluck('riddle_id')
                    ->toArray();

                // Parcourir les lieux dans l'ordre de la session pour trouver le premier ayant encore des énigmes de la bonne difficulté non résolues
                foreach ($session->sessionPlaces as $sessionPlace) {
                    $placeRiddle = \App\Models\Riddle::where('place_id', $sessionPlace->place_id)
                        ->where('difficulty', $session->difficulty)
                        ->whereNotIn('id', $solvedRiddleIds)
                        ->get()
                        ->sortBy(function($r) use ($session) {
                            return md5($r->id . '_' . $session->id);
                        })
                        ->first();
                    if ($placeRiddle) {
                        $nextRiddleId = $placeRiddle->id;
                        break;
                    }
                }
            }

            // 8. Achievements
            $this->checkAchievements($user, $session, $score);

            // 9. Événement temps réel (uniquement si un canal valide existe)
            try {
                event(new RiddleResolvedEvent($score));
            } catch (\Exception $e) {
                Log::warning('Broadcast RiddleResolvedEvent échoué : ' . $e->getMessage());
            }

            return response()->json([
                'score' => $scoreData['total'],
                'is_finished' => $isFinished,
                'next_riddle_id' => $nextRiddleId,
                'session_id' => $session->id,
                'details' => array_merge($scoreData, ['time_taken' => $timeTakenSeconds])
            ]);
        });
    }

    /**
     * Permet à l'équipe de passer à l'énigme suivante (avec 0 points).
     */
    public function skip(Request $request, Riddle $riddle)
    {
        $user = $request->user();
        $session = GameSession::whereHas('gamePlayers', function($query) use ($user) {
                $query->where('user_id', $user->id)->where('is_active', true);
            })
            ->where('status', 'active')
            ->firstOrFail();

        // 1. Marquer le lieu comme complété (non résolu avec score à 0)
        SessionPlace::where('game_session_id', $session->id)
            ->where('place_id', $riddle->place_id)
            ->update([
                'is_completed' => true,
                'completed_at' => now(),
            ]);

        // 2. Création d'un enregistrement de score de 0 point pour le bilan détaillé
        Score::create([
            'game_session_id'    => $session->id,
            'user_id'            => $session->mode === 'mercenaire' ? $user->id : null,
            'riddle_id'          => $riddle->id,
            'points_earned'      => 0,
            'points_speed'       => 0,
            'points_distance'    => 0,
            'points_hints_bonus' => 0,
            'points_difficulty'  => 0,
            'hints_used'         => 0,
            'time_taken_seconds' => 0,
            'distance_m'         => 0,
            'resolved_at'        => now(),
        ]);

        $session->increment('solved_places');
        $session->refresh();

        if ($session->solved_places >= $session->total_places) {
            $session->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            return redirect()->route('player.game-sessions.summary', $session);
        } else {
            $session->increment('current_place_index');
            // Trouver la prochaine énigme du lieu suivant
            $nextPlace = $session->sessionPlaces()
                ->where('order_index', $session->current_place_index)
                ->first();

            if ($nextPlace) {
                $nextRiddle = Riddle::where('place_id', $nextPlace->place_id)->first();
                if ($nextRiddle) {
                    return redirect()->route('player.riddle.show', $nextRiddle);
                }
            }
        }

        return redirect()->route('player.dashboard');
    }
}
