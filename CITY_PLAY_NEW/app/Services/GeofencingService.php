<?php

namespace App\Services;

class GeofencingService
{
    /**
     * Calcule la distance en mètres entre deux points GPS en utilisant la formule de Haversine.
     *
     * @param float $lat1 Latitude du point 1
     * @param float $lon1 Longitude du point 1
     * @param float $lat2 Latitude du point 2
     * @param float $lon2 Longitude du point 2
     * @return float Distance en mètres
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Rayon de la Terre en mètres

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Vérifie si l'utilisateur est dans le rayon de validation du lieu.
     *
     * @param float $userLat
     * @param float $userLon
     * @param float $placeLat
     * @param float $placeLon
     * @param float $validationRadius Rayon en mètres (ex: 50)
     * @return bool
     */
    public function isWithinRadius(float $userLat, float $userLon, float $placeLat, float $placeLon, float $validationRadius = 50): bool
    {
        $distance = $this->calculateDistance($userLat, $userLon, $placeLat, $placeLon);
        return $distance <= $validationRadius;
    }
}
