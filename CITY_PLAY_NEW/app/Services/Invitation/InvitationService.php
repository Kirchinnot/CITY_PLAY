<?php

namespace App\Services\Invitation;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InvitationService
{
    /**
     * Génère une nouvelle invitation pour une ville donnée.
     *
     * @param int $cityId
     * @param User $creator
     * @param array $params
     * @return Invitation
     */
    public function generate(int $cityId, User $creator, array $params = []): Invitation
    {
        return Invitation::create([
            'token'            => Str::random(64),
            'city_id'          => $cityId,
            'created_by'       => $creator->id,
            'mode'             => $params['mode'] ?? 'collectif',
            'difficulty'       => $params['difficulty'] ?? 'moyen',
            'locomotion'       => $params['locomotion'] ?? 'marche',
            'max_players'      => $params['max_players'] ?? null,
            'duration_minutes' => $params['duration_minutes'] ?? 90,
            'expires_at'       => isset($params['expires_in_hours']) 
                                    ? Carbon::now()->addHours($params['expires_in_hours']) 
                                    : null,
            'used_count'       => 0,
        ]);
    }

    /**
     * Valide un token d'invitation et retourne l'invitation si elle est valide.
     *
     * @param string $token
     * @return Invitation|null
     */
    public function validateToken(string $token): ?Invitation
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation || $invitation->isExpired()) {
            return null;
        }

        return $invitation;
    }

    /**
     * Incrémente le compteur d'utilisation d'une invitation.
     *
     * @param Invitation $invitation
     * @return void
     */
    public function incrementUsage(Invitation $invitation): void
    {
        $invitation->increment('used_count');
    }

    /**
     * Génère l'URL complète d'invitation pour le QR Code.
     *
     * @param Invitation $invitation
     * @return string
     */
    public function getInvitationUrl(Invitation $invitation): string
    {
        return route('game.join', ['token' => $invitation->token]);
    }
}
