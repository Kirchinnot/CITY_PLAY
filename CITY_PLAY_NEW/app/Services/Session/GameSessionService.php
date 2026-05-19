<?php

namespace App\Services\Session;

use App\Models\Invitation;
use App\Models\GameSession;
use App\Models\User;
use App\Models\GamePlayer;
use App\Events\PlayerJoined;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\Place;
use App\Models\SessionPlace;

class GameSessionService
{
    /**
     * Facteurs de vitesse selon le mode de locomotion.
     * Basé sur une vitesse de marche de référence (1.0).
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
     * Gère la jointure d'un utilisateur à une session via une invitation.
     * Crée la session si elle n'existe pas encore.
     */
    public function joinOrCreateSession(Invitation $invitation, User $user): GameSession
    {
        return DB::transaction(function () use ($invitation, $user) {
            // 1. Chercher une session 'pending' ou 'active' liée à cette invitation
            $session = GameSession::where('invitation_id', $invitation->id)
                ->whereIn('status', ['pending', 'active'])
                ->first();

            // 2. Si aucune session n'existe, on la crée (l'utilisateur devient le host)
            if (!$session) {
                $session = GameSession::create([
                    'invitation_id'     => $invitation->id,
                    'city_id'           => $invitation->city_id,
                    'host_user_id'      => $user->id,
                    'mode'              => $invitation->mode,
                    'difficulty'        => $invitation->difficulty,
                    'locomotion'        => $invitation->locomotion,
                    'available_minutes' => $invitation->duration_minutes,
                    'status'            => 'pending',
                ]);
            }

            // 3. Ajouter l'utilisateur aux joueurs de la session s'il n'y est pas déjà
            $isAlreadyIn = $session->players()->where('user_id', $user->id)->exists();
            
            if (!$isAlreadyIn) {
                $session->players()->attach($user->id, [
                    'joined_at'    => Carbon::now(),
                    'is_active'    => true,
                    'last_seen_at' => Carbon::now(),
                ]);

                // 4. Déclencher l'événement temps réel
                broadcast(new PlayerJoined($session, $user))->toOthers();
            }

            return $session;
        });
    }

    /**
     * Crée une session directement (sans invitation préalable externe).
     * Utile pour le Dashboard ou le mode "Quick Start".
     */
    public function createDirectSession(User $user, array $data): GameSession
    {
        return DB::transaction(function () use ($user, $data) {
            // 1. Créer une invitation "système" interne
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

            // 2. Créer la session
            $session = GameSession::create([
                'invitation_id'     => $invitation->id,
                'city_id'           => $data['city_id'],
                'start_place_id'    => $data['start_place_id'] ?? null,
                'host_user_id'      => $user->id,
                'mode'              => $invitation->mode,
                'difficulty'        => $invitation->difficulty,
                'locomotion'        => $invitation->locomotion,
                'available_minutes' => $invitation->duration_minutes,
                'status'            => 'pending', // On passe par le lobby d'abord
            ]);

            // 3. Ajouter le créateur comme joueur
            $session->players()->attach($user->id, [
                'joined_at'    => Carbon::now(),
                'is_active'    => true,
                'last_seen_at' => Carbon::now(),
            ]);

            return $session;
        });
    }

    /**
     * Démarre officiellement la partie.
     * Calcule et sélectionne les lieux en fonction de la durée et de la locomotion.
     */
    public function startSession(GameSession $session, ?int $startPlaceId = null): bool
    {
        if ($session->status !== 'pending') {
            return false;
        }

        return DB::transaction(function () use ($session, $startPlaceId) {
            // 1. Sélectionner les lieux
            $this->initializeSessionPlaces($session, $startPlaceId);

            // 2. Mettre à jour le statut
            return $session->update([
                'status'     => 'active',
                'started_at' => Carbon::now(),
            ]);
        });
    }

    /**
     * Sélectionne les lieux qui feront partie de la session.
     */
    protected function initializeSessionPlaces(GameSession $session, ?int $startPlaceId = null): void
    {
        $query = Place::where('city_id', $session->city_id)
            ->orderBy('order_index');

        $allPlaces = $query->get();
        
        // Si un point de départ est spécifié, on réordonne la liste pour commencer par ce lieu
        if ($startPlaceId) {
            $startPlace = $allPlaces->firstWhere('id', $startPlaceId);
            if ($startPlace) {
                $otherPlaces = $allPlaces->reject(fn($p) => $p->id === $startPlaceId);
                $allPlaces = collect([$startPlace])->concat($otherPlaces);
            }
        }

        $locomotionFactor = self::LOCOMOTION_FACTORS[$session->locomotion] ?? 1.0;
        $remainingMinutes = $session->available_minutes;
        $selectedCount = 0;

        foreach ($allPlaces as $place) {
            // Calcul du temps pour ce lieu : temps de trajet + temps de résolution
            $travelTime = $place->estimated_time_min * $locomotionFactor;
            $totalPlaceTime = $travelTime + self::AVG_RIDDLE_SOLVE_TIME;

            if ($remainingMinutes >= $totalPlaceTime || $selectedCount === 0) {
                SessionPlace::create([
                    'game_session_id' => $session->id,
                    'place_id'        => $place->id,
                    'order_index'     => $selectedCount, // On commence à 0
                ]);

                $remainingMinutes -= $totalPlaceTime;
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

    /**
     * Met la partie en pause.
     */
    public function pauseSession(GameSession $session): bool
    {
        if ($session->status !== 'active') {
            return false;
        }

        return $session->update([
            'status'    => 'paused',
            'paused_at' => Carbon::now(),
        ]);
    }

    /**
     * Reprend la partie après une pause.
     */
    public function resumeSession(GameSession $session): bool
    {
        if ($session->status !== 'paused' || !$session->paused_at) {
            return false;
        }

        $pauseDuration = $session->paused_at->diffInSeconds(Carbon::now());

        return $session->update([
            'status'              => 'active',
            'paused_at'           => null,
            'total_pause_seconds' => $session->total_pause_seconds + $pauseDuration,
        ]);
    }

    /**
     * Abandonne la partie.
     */
    public function abandonSession(GameSession $session): bool
    {
        if ($session->status === 'completed' || $session->status === 'abandoned') {
            return false;
        }

        return $session->update([
            'status'       => 'abandoned',
            'completed_at' => Carbon::now(),
        ]);
    }

    /**
     * Termine la partie normalement.
     */
    public function completeSession(GameSession $session): bool
    {
        if ($session->status !== 'active') {
            return false;
        }

        return $session->update([
            'status'       => 'completed',
            'completed_at' => Carbon::now(),
        ]);
    }

    /**
     * Met à jour les paramètres de la session (Host uniquement).
     */
    public function updateSettings(GameSession $session, array $settings): bool
    {
        if ($session->status !== 'pending') {
            return false;
        }

        return $session->update([
            'difficulty'        => $settings['difficulty'] ?? $session->difficulty,
            'locomotion'        => $settings['locomotion'] ?? $session->locomotion,
            'available_minutes' => $settings['available_minutes'] ?? $session->available_minutes,
            'mode'              => $settings['mode'] ?? $session->mode,
            'team_size'         => $settings['team_size'] ?? $session->team_size,
        ]);
    }
}
