<?php

namespace App\Support;

use App\Models\GameSession;
use Carbon\Carbon;

class SessionTimer
{
    /**
     * Secondes de jeu actives écoulées (hors pauses cumulées).
     */
    public static function elapsedActiveSeconds(GameSession $session): int
    {
        if (!$session->started_at) {
            return 0;
        }

        $end = $session->isPaused() && $session->paused_at
            ? Carbon::parse($session->paused_at)
            : now();

        $elapsed = (int) Carbon::parse($session->started_at)->diffInSeconds($end);

        return max(0, $elapsed - (int) ($session->total_pause_seconds ?? 0));
    }

    public static function remainingSeconds(GameSession $session): int
    {
        if (!$session->started_at) {
            return ($session->available_minutes ?? 0) * 60;
        }

        $limit = (int) ($session->available_minutes ?? 0) * 60;

        if ($limit <= 0) {
            return 0;
        }

        return max(0, $limit - self::elapsedActiveSeconds($session));
    }

    public static function isExpired(GameSession $session): bool
    {
        return $session->started_at !== null && self::remainingSeconds($session) <= 0;
    }

    /**
     * Termine la session si le délai est dépassé.
     */
    public static function expireIfNeeded(GameSession $session): bool
    {
        if (!$session->isActive() && !$session->isPaused()) {
            return false;
        }

        if (!self::isExpired($session)) {
            return false;
        }

        return $session->update([
            'status'       => 'completed',
            'completed_at' => now(),
            'paused_at'    => null,
        ]);
    }

    /**
     * Données chrono pour le frontend (compte à rebours client).
     */
    public static function toFrontendArray(GameSession $session): array
    {
        self::expireIfNeeded($session);
        $session->refresh();

        return [
            'available_seconds'   => (int) ($session->available_minutes ?? 0) * 60,
            'remaining_seconds'   => self::remainingSeconds($session),
            'elapsed_seconds'     => self::elapsedActiveSeconds($session),
            'is_expired'          => self::isExpired($session) || $session->isCompleted(),
            'started_at'          => $session->started_at?->toIso8601String(),
            'paused_at'           => $session->paused_at?->toIso8601String(),
            'total_pause_seconds' => (int) ($session->total_pause_seconds ?? 0),
            'status'              => $session->status,
        ];
    }
}
