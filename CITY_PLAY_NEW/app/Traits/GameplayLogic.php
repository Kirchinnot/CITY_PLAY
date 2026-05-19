<?php

namespace App\Traits;

use App\Models\Riddle;
use App\Models\SessionPlace;
use Carbon\Carbon;

trait GameplayLogic
{
    /**
     * Calcule la distance entre deux points GPS en utilisant la formule Haversine.
     * Retourne la distance en mètres.
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // Rayon de la Terre en mètres

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Compare deux chaînes de caractères avec une tolérance aux fautes de frappe.
     * Utilise la distance de Levenshtein.
     */
    public function compareText(string $answer, string $target, int $maxTolerance = 2): bool
    {
        $answer = strtolower(trim($answer));
        $target = strtolower(trim($target));

        if ($answer === $target) {
            return true;
        }

        $distance = levenshtein($answer, $target);

        return $distance <= $maxTolerance;
    }

    /**
     * Calcule la vitesse en km/h entre deux points.
     */
    public function calculateSpeed(float $distanceMeters, int $timeSeconds): float
    {
        if ($timeSeconds <= 0) {
            return 0;
        }

        // Distance en km / temps en heures
        $distanceKm = $distanceMeters / 1000;
        $timeHours = $timeSeconds / 3600;

        return $distanceKm / $timeHours;
    }

    /**
     * Calcule le score final simplifié pour une énigme résolue.
     */
    public function calculatePoints($riddle, $session): int
    {
        // 1. Points de base par difficulté
        $points = match ($riddle->difficulty) {
            'enfant' => 50,
            'facile' => 100,
            'moyen' => 150,
            'difficile' => 200,
            default => 100,
        };

        // 2. Malus indices (ex: -20 pts par indice utilisé)
        $hintsUsed = \App\Models\HintUsage::where('game_session_id', $session->id)
            ->where('riddle_id', $riddle->id)
            ->count();
        
        $malus = $hintsUsed * 20;

        return (int) max(10, $points - $malus);
    }

    /**
     * Vérifie si des succès doivent être débloqués.
     */
    public function checkAchievements($user, $session, $score): void
    {
        // 1. "Premier lieu validé"
        if ($session->scores()->count() === 1) {
            \App\Models\Achievement::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'game_session_id' => $session->id,
                    'type' => 'explorateur',
                ],
                ['earned_at' => now()]
            );
        }

        // 2. "Rapidité extrême" (moins de 60s)
        if ($score->time_taken_seconds < 60) {
            \App\Models\Achievement::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'game_session_id' => $session->id,
                    'type' => 'rapide',
                ],
                ['earned_at' => now()]
            );
        }

        // 3. "Sans indice" — aucun indice utilisé sur toute la session
        $totalHintsUsed = $session->scores()->sum('hints_used');
        if ($totalHintsUsed === 0 && $score->hints_used === 0) {
            \App\Models\Achievement::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'game_session_id' => $session->id,
                    'type' => 'sans_indice',
                ],
                ['earned_at' => now()]
            );
        }
    }
}
