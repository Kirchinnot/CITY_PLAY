<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $session = null;
        $cities = [];
        $adminStats = [];

        if ($user) {
            // Données pour le JOUEUR : Session active ou la plus récente terminée
            $session = \App\Models\GameSession::whereHas('gamePlayers', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->whereIn('status', ['active', 'completed'])
                ->with(['city'])
                ->latest()
                ->first();

            if ($session && $session->status === 'active') {
                // On récupère l'énigme actuelle via session_places uniquement pour les sessions actives
                $currentPlace = $session->sessionPlaces()
                    ->where('order_index', $session->current_place_index)
                    ->first();
                
                if ($currentPlace) {
                    // On récupère la première énigme de ce lieu selon la difficulté de la session
                    // Si aucune énigme de cette difficulté, on prend la première disponible
                    $riddle = \App\Models\Riddle::where('place_id', $currentPlace->place_id)
                        ->where('difficulty', $session->difficulty)
                        ->with(['hints', 'images'])
                        ->first();

                    if (!$riddle) {
                        $riddle = \App\Models\Riddle::where('place_id', $currentPlace->place_id)
                            ->with(['hints', 'images'])
                            ->first();
                    }

                    $session->current_riddle = $riddle;
                }

                // Score total de la session
                $session->total_score = $session->scores()->sum('points_earned');
                // Nombre de badges débloqués
                $session->achievements_count = $session->achievements()->count();
            }

            // Liste des villes disponibles pour l'accueil
            $cities = \App\Models\City::withCount('places')->get()->map(function($city) {
                return [
                    'id' => $city->id,
                    'name' => $city->name,
                    'riddles' => $city->places_count, // Nombre de lieux/énigmes
                    'duration' => '2h', // À dynamiser plus tard si besoin
                    'tag' => 'Découverte',
                    'color' => 'bg-blue-900/40',
                    'icon' => 'M12 2L2 7l10 5 10-5-10-5z'
                ];
            });

            // Données pour l'ADMIN
            if ($user->role === 'admin') {
                $adminStats = [
                    'active_sessions' => \App\Models\GameSession::where('status', 'active')->count(),
                    'total_players' => \App\Models\User::where('role', 'player')->count(),
                    'suspicious_logs' => \App\Models\PositionLog::where('is_suspicious', true)->count(),
                ];
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'session' => $session,
            'cities' => $cities,
            'adminStats' => $adminStats,
            'flash' => [
                'result' => $request->session()->get('result'),
            ],
        ];
    }
}
