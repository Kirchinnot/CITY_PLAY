<?php

namespace App\Services\Session;

use App\Models\GameSession;
use App\Models\Invitation;
use App\Models\Place;
use App\Models\Riddle;
use App\Models\Score;
use App\Models\SessionPlace;
use App\Models\User;
use App\Events\PlayerJoined;
use App\Events\RiddleResolvedEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GameSessionService
{
    /**
     * Facteurs de vitesse selon le mode de locomotion.
     */
    const LOCOMOTION_FACTORS = [
        'marche'  => 1.0,
        'velo'    => 0.4,
        'moto'    => 0.25,
        'voiture' => 0.2,
    ];

    /**
     * Temps moyen estimé pour résoudre une énigme (en minutes).
     */
    const AVG_RIDDLE_SOLVE_TIME = 5;

    /**
     * Crée une session directement (Quick Start).
     */
    public function createDirectSession(User $user, array $data): GameSession
    {
        return DB::transaction(function () use ($user, $data) {
            $invitation = Invitation::create([
                'token'            => (string) Str::uuid(),
                'city_id'          => $data['city_id'],
                'created_by'       => $user->id,
                'mode'             => $data['mode'] ?? 'collectif',
                'difficulty'       => $data['difficulty'] ?? 'moyen',
                'locomotion'       => $data['locomotion'] ?? 'marche',
                'max_players'      => 10,
                'duration_minutes' => $data['available_minutes'] ?? 120,
            ]);

            $session = GameSession::create([
                'invitation_id'     => $invitation->id,
                'city_id'           => $data['city_id'],
                'start_place_id'    => $data['start_place_id'] ?? null,
                'host_user_id'      => $user->id,
                'mode'              => $invitation->mode,
                'team_size'         => $data['team_size'] ?? 1,
                'difficulty'        => $invitation->difficulty,
                'locomotion'        => $invitation->locomotion,
                'available_minutes' => $invitation->duration_minutes,
                'status'            => 'pending',
            ]);

            $session->players()->attach($user->id, [
                'joined_at'    => Carbon::now(),
                'is_active'    => true,
                'last_seen_at' => Carbon::now(),
            ]);

            return $session;
        });
    }

    /**
     * Gère la jointure d'un utilisateur à une session via une invitation.
     */
    public function joinOrCreateSession(Invitation $invitation, User $user): GameSession
    {
        return DB::transaction(function () use ($invitation, $user) {
            $session = GameSession::where('invitation_id', $invitation->id)
                ->whereIn('status', ['pending', 'active'])
                ->first();

            if (!$session) {
                $session = GameSession::create([
                    'invitation_id'     => $invitation->id,
                    'city_id'           => $invitation->city_id,
                    'host_user_id'      => $user->id,
                    'mode'              => $invitation->mode,
                    'team_size'         => 1,
                    'difficulty'        => $invitation->difficulty,
                    'locomotion'        => $invitation->locomotion,
                    'available_minutes' => $invitation->duration_minutes,
                    'status'            => 'pending',
                ]);
            }

            if (!$session->players()->where('user_id', $user->id)->exists()) {
                $session->players()->attach($user->id, [
                    'joined_at'    => Carbon::now(),
                    'is_active'    => true,
                    'last_seen_at' => Carbon::now(),
                ]);
                broadcast(new PlayerJoined($session, $user))->toOthers();
            }

            return $session;
        });
    }

    /**
     * Démarre officiellement la partie.
     */
    public function startSession(GameSession $session, ?int $startPlaceId = null): bool
    {
        if ($session->status !== 'pending') return false;

        return DB::transaction(function () use ($session, $startPlaceId) {
            $this->initializeSessionPlaces($session, $startPlaceId);
            return $session->update([
                'status'     => 'active',
                'started_at' => Carbon::now(),
            ]);
        });
    }

    /**
     * Met la session en pause.
     */
    public function pauseSession(GameSession $session): bool
    {
        if (!$session->isActive()) return false;

        return $session->update([
            'status' => 'paused',
            'paused_at' => Carbon::now(),
        ]);
    }

    /**
     * Reprend la session.
     */
    public function resumeSession(GameSession $session): bool
    {
        if (!$session->isPaused()) return false;

        $pausedAt = $session->paused_at;
        $now = Carbon::now();
        $pauseDuration = $pausedAt ? $now->diffInSeconds($pausedAt) : 0;

        return $session->update([
            'status' => 'active',
            'paused_at' => null,
            'total_pause_seconds' => $session->total_pause_seconds + $pauseDuration,
        ]);
    }

    /**
     * Abandonne la session.
     */
    public function abandonSession(GameSession $session): bool
    {
        if ($session->isFinished()) {
            return false;
        }

        if ($session->isPaused()) {
            $session->accumulateOpenPause();
            $session->save();
        }

        return $session->update([
            'status' => 'abandoned',
            'completed_at' => Carbon::now(),
        ]);
    }

    /**
     * Termine la session car le temps de jeu est écoulé.
     */
    public function expireSession(GameSession $session): bool
    {
        if ($session->isFinished() || !$session->isTimeExpired()) {
            return false;
        }

        if ($session->isPaused()) {
            $session->accumulateOpenPause();
            $session->save();
        }

        return $session->update([
            'status' => 'completed',
            'completed_at' => Carbon::now(),
        ]);
    }

    /**
     * Synchronise l'état du timer et applique l'expiration si nécessaire.
     */
    public function syncTimerState(GameSession $session): array
    {
        $session->refresh();

        if ($session->isInProgress() && $session->isTimeExpired()) {
            $this->expireSession($session);
            $session->refresh();
        }

        return $session->toTimerArray();
    }

    /**
     * Heartbeat joueur — trace la dernière activité.
     */
    public function touchPlayerPresence(GameSession $session, User $user): void
    {
        if (!$session->players()->where('user_id', $user->id)->exists()) {
            return;
        }

        $session->players()->updateExistingPivot($user->id, [
            'last_seen_at' => Carbon::now(),
        ]);
    }

    /**
     * Calcule le score final pour une énigme résolue.
     */
    public function calculatePoints(Riddle $riddle, GameSession $session): int
    {
        // 1. Points de base par difficulté
        $points = match ($riddle->difficulty) {
            'enfant' => 50,
            'facile' => 100,
            'moyen' => 150,
            'difficile' => 200,
            default => 100,
        };

        // 2. Malus indices
        $hintsUsed = \App\Models\HintUsage::where('game_session_id', $session->id)
            ->where('riddle_id', $riddle->id)
            ->count();
        
        $malus = $hintsUsed * 25;

        // 3. Bonus temps (si résolu en moins de 2 minutes)
        $bonus = 0;
        $lastAction = Score::where('game_session_id', $session->id)->latest()->first();
        $startTime = $lastAction ? $lastAction->created_at : $session->started_at;
        
        if ($startTime && Carbon::now()->diffInMinutes($startTime) < 2) {
            $bonus = 30;
        }

        return (int) max(10, $points - $malus + $bonus);
    }

    /**
     * Vérifie et débloque les succès.
     */
    public function checkAchievements(User $user, GameSession $session): void
    {
        // Exemple : "Explorateur" pour le premier lieu
        if ($session->scores()->count() === 1) {
            \App\Models\Achievement::firstOrCreate([
                'user_id' => $user->id,
                'game_session_id' => $session->id,
                'type' => 'explorateur',
            ], ['earned_at' => Carbon::now()]);
        }

        // Exemple : "Maître des énigmes" si tous les lieux sont complétés
        if ($session->isCompleted()) {
             \App\Models\Achievement::firstOrCreate([
                'user_id' => $user->id,
                'game_session_id' => $session->id,
                'type' => 'maitre',
            ], ['earned_at' => Carbon::now()]);
        }
    }

    /**
     * Valide la résolution d'une énigme et gère la progression.
     */
    public function resolveRiddle(GameSession $session, Riddle $riddle, User $user, ?int $points = null): bool
    {
        return DB::transaction(function () use ($session, $riddle, $user, $points) {
            $finalPoints = $points ?? $this->calculatePoints($riddle, $session);

            // 1. Enregistrer le score
            $score = Score::create([
                'game_session_id' => $session->id,
                'user_id'         => $user->id,
                'riddle_id'       => $riddle->id,
                'points_earned'   => $finalPoints,
            ]);

            // 2. Marquer le lieu comme complété
            $sessionPlace = $session->sessionPlaces()->where('place_id', $riddle->place_id)->first();
            if ($sessionPlace) {
                $sessionPlace->update(['is_completed' => true]);
            }

            // 3. Mettre à jour les compteurs de la session
            $solvedCount = $session->sessionPlaces()->where('is_completed', true)->count();
            
            // 4. Vérifier les succès
            $this->checkAchievements($user, $session);

            // 5. Calculer la suite
            $nextPlace = $session->sessionPlaces()
                ->where('is_completed', false)
                ->orderBy('order_index')
                ->first();

            if ($nextPlace) {
                $session->update([
                    'current_place_index' => $nextPlace->order_index,
                    'solved_places' => $solvedCount,
                ]);
                broadcast(new RiddleResolvedEvent($score))->toOthers();
            } else {
                $session->update(['solved_places' => $solvedCount]);
                $this->completeSession($session);
            }

            return true;
        });
    }

    /**
     * Termine la session normalement.
     */
    public function completeSession(GameSession $session): bool
    {
        if ($session->isFinished()) return false;

        return $session->update([
            'status' => 'completed',
            'completed_at' => Carbon::now(),
        ]);
    }

    /**
     * Génère les données du bilan pour une session.
     */
    public function getSummaryData(GameSession $session): array
    {
        $session->load(['city', 'scores.riddle.place.images', 'achievements', 'sessionPlaces.place.images']);

        $totalTime = 0;
        if ($session->started_at && $session->completed_at) {
            $totalTime = $session->started_at->diffInSeconds($session->completed_at) - $session->total_pause_seconds;
        }

        $unsolvedPlaces = $session->sessionPlaces()
            ->where('is_completed', false)
            ->with('place.images')
            ->get()
            ->map(fn($sp) => $sp->place);

        return [
            'id' => $session->id,
            'city_name' => $session->city?->name ?? 'Ville inconnue',
            'outro_config' => $session->city?->outro_config,
            'total_score' => $session->scores->sum('points_earned'),
            'total_time' => max(0, $totalTime),
            'solved_places' => $session->sessionPlaces()->where('is_completed', true)->count(),
            'total_places' => $session->sessionPlaces()->count(),
            'difficulty' => $session->difficulty,
            'mode' => $session->mode,
            'status' => $session->status,
            'unsolved_places' => $unsolvedPlaces,
            'scores' => $session->scores,
            'achievements' => $session->achievements,
        ];
    }

    /**
     * Sélectionne et ordonne les lieux de la session.
     */
    protected function initializeSessionPlaces(GameSession $session, ?int $startPlaceId = null): void
    {
        // On ne sélectionne que les lieux qui possèdent une énigme pour la difficulté choisie
        $allPlaces = Place::where('city_id', $session->city_id)
            ->whereHas('riddles', function($q) use ($session) {
                $q->where('difficulty', $session->difficulty);
            })
            ->orderBy('order_index')
            ->get();
        
        if ($startPlaceId) {
            $startPlace = $allPlaces->firstWhere('id', $startPlaceId);
            if ($startPlace) {
                $allPlaces = collect([$startPlace])->concat($allPlaces->reject(fn($p) => $p->id === $startPlaceId));
            }
        }

        $locomotionFactor = self::LOCOMOTION_FACTORS[$session->locomotion] ?? 1.0;
        $remainingMinutes = $session->available_minutes;
        $selectedCount = 0;

        foreach ($allPlaces as $place) {
            $totalTime = ($place->estimated_time_min * $locomotionFactor) + self::AVG_RIDDLE_SOLVE_TIME;

            if ($remainingMinutes >= $totalTime || $selectedCount === 0) {
                SessionPlace::create([
                    'game_session_id' => $session->id,
                    'place_id'        => $place->id,
                    'order_index'     => $selectedCount,
                    'is_completed'    => false,
                ]);
                $remainingMinutes -= $totalTime;
                $selectedCount++;
            } else {
                break;
            }
        }

        $session->update([
            'total_places' => $selectedCount,
            'current_place_index' => 0,
        ]);
    }
}
