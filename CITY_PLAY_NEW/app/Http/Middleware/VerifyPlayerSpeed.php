<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\PositionLog;
use App\Traits\GameplayLogic;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyPlayerSpeed
{
    use GameplayLogic;

    /**
     * Intercepte la requête pour analyser la vitesse de déplacement du joueur.
     * Détecte les anomalies de triche GPS (ex: spoofing GPS, téléportations).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Ne traite la requête que si les coordonnées GPS et la session sont fournies
        if (!$request->has(['latitude', 'longitude', 'session_id'])) {
            return $next($request);
        }

        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        $sessionId = $request->input('session_id');
        $latitude  = (float) $request->input('latitude');
        $longitude = (float) $request->input('longitude');

        // 2. Récupère la dernière position enregistrée du joueur
        $gamePlayer = GamePlayer::where('game_session_id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        if ($gamePlayer && $gamePlayer->last_lat && $gamePlayer->last_lng) {
            // Calcule la distance parcourue depuis le dernier signal
            $distMeters = $this->calculateDistance(
                (float) $gamePlayer->last_lat,
                (float) $gamePlayer->last_lng,
                $latitude,
                $longitude
            );

            // Calcule le temps écoulé en secondes
            $timeSeconds = Carbon::parse($gamePlayer->last_seen_at)->diffInSeconds(now());
            
            // Calcule la vitesse moyenne de déplacement en km/h
            $speed = $this->calculateSpeed($distMeters, $timeSeconds);

            // Seuil de détection : 120 km/h max autorisés (suspicion de téléportation GPS)
            $maxAllowedSpeed = 120.0;
            $isSuspicious = $speed > $maxAllowedSpeed;

            // Enregistrement télémétrique
            PositionLog::create([
                'user_id' => $user->id,
                'game_session_id' => $sessionId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'speed' => $speed,
                'is_suspicious' => $isSuspicious,
            ]);

            // Si suspicion de triche, bloque immédiatement la requête
            if ($isSuspicious) {
                return response()->json([
                    'message' => sprintf(
                        'Déplacement trop rapide détecté (%d km/h supérieur à la limite de %d km/h). Tentative ignorée.',
                        round($speed),
                        $maxAllowedSpeed
                    )
                ], 403);
            }
        } else {
            // Premier point ou coordonnées non renseignées précédemment
            PositionLog::create([
                'user_id' => $user->id,
                'game_session_id' => $sessionId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'speed' => 0.0,
                'is_suspicious' => false,
            ]);
        }

        // 3. Met à jour la position active dans le pivot de session
        if ($gamePlayer) {
            $gamePlayer->update([
                'last_lat' => $latitude,
                'last_lng' => $longitude,
                'last_seen_at' => now(),
            ]);
        }

        return $next($request);
    }
}
