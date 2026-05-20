<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

class ReactivateGamePlayer
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        if (! $user || ! $user->id) {
            return;
        }

        // Réactive les entrées game_players pour cet utilisateur
        // uniquement pour les sessions encore 'active'.
        DB::table('game_players')
            ->join('game_sessions', 'game_players.game_session_id', '=', 'game_sessions.id')
            ->where('game_players.user_id', $user->id)
            ->where('game_sessions.status', 'active')
            ->where('game_players.is_active', false)
            ->update(['game_players.is_active' => true, 'game_players.last_seen_at' => now()]);
    }
}
