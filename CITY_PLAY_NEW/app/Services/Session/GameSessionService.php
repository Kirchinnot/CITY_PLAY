<?php

namespace App\Services\Session;

use App\Models\Invitation;
use App\Models\GameSession;
use App\Models\User;
use App\Models\GamePlayer;
use App\Events\PlayerJoined;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GameSessionService
{
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
     * Démarre officiellement la partie.
     */
    public function startSession(GameSession $session): bool
    {
        if ($session->status !== 'pending') {
            return false;
        }

        return $session->update([
            'status'     => 'active',
            'started_at' => Carbon::now(),
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
}
