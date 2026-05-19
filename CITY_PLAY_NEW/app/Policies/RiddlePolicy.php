<?php

namespace App\Policies;

use App\Models\Riddle;
use App\Models\User;
use App\Models\GameSession;

class RiddlePolicy
{
    /**
     * Détermine si l'utilisateur peut voir et résoudre cette énigme dans sa session actuelle.
     */
    public function solve(User $user, Riddle $riddle): bool
    {
        // On récupère la session active de l'utilisateur
        $session = GameSession::whereHas('players', function($q) use ($user) {
            $q->where('user_id', $user->id)->where('is_active', true);
        })->where('status', 'active')->first();

        if (!$session) return false;

        // L'énigme doit appartenir au lieu actuel de la session
        $currentPlaceId = $session->sessionPlaces()
            ->where('order_index', $session->current_place_index)
            ->value('place_id');

        return $riddle->place_id === $currentPlaceId;
    }
}
