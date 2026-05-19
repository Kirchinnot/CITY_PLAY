<?php

namespace App\Policies;

use App\Models\GameSession;
use App\Models\User;

class GameSessionPolicy
{
    /**
     * Détermine si l'utilisateur peut voir la session.
     */
    public function view(User $user, GameSession $session): bool
    {
        return $session->players()->where('user_id', $user->id)->exists();
    }

    /**
     * Détermine si l'utilisateur peut gérer la session (Hôte uniquement).
     */
    public function manage(User $user, GameSession $session): bool
    {
        return $session->host_user_id === $user->id;
    }

    /**
     * Détermine si l'utilisateur peut jouer dans cette session.
     */
    public function play(User $user, GameSession $session): bool
    {
        return $session->isActive() && $session->players()->where('user_id', $user->id)->exists();
    }
}
