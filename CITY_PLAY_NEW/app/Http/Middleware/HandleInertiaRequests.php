<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\GameSession;
use App\Models\City;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $gameState = null;

        if ($user) {
            // Récupérer la session la plus pertinente pour le joueur
            $session = GameSession::whereHas('players', fn($q) => $q->where('user_id', $user->id))
                ->whereIn('status', ['active', 'paused', 'pending'])
                ->with(['city'])
                ->latest()
                ->first();

            if ($session) {
                $gameState = [
                    'id' => $session->id,
                    'status' => $session->status,
                    'city' => $session->city,
                    'current_place_index' => $session->current_place_index,
                    'total_places' => $session->total_places,
                    'solved_places' => $session->solved_places,
                    'total_score' => $session->total_score,
                    'current_riddle' => $session->current_riddle,
                    'is_host' => $session->host_user_id === $user->id,
                    'timer' => [
                        'started_at' => $session->started_at,
                        'paused_at' => $session->paused_at,
                        'total_pause_seconds' => $session->total_pause_seconds,
                        'available_minutes' => $session->available_minutes,
                    ]
                ];
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'gameState' => $gameState,
            'cities' => $user ? City::with(['places.riddles'])->withCount('places')->get() : [],
            'flash' => [
                'message' => $request->session()->get('message'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
