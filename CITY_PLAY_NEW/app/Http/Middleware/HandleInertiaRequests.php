<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\City;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\PositionLog;
use App\Services\Session\GameSessionService;

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
                $timerService = app(GameSessionService::class);
                $timerService->touchPlayerPresence($session, $user);
                $timer = $timerService->syncTimerState($session);
                $session->refresh();

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
                    'available_minutes' => $session->available_minutes,
                    'timer' => $timer,
                ];
            }
        }

        $adminStats = null;

        if ($user && $user->role === 'admin') {
            $ongoingStatuses = ['pending', 'active', 'paused'];
            $activeSessionCount = GameSession::whereIn('status', $ongoingStatuses)->count();

            $adminStats = [
                'active_sessions' => $activeSessionCount,
                'pending_sessions' => GameSession::where('status', 'pending')->count(),
                'paused_sessions' => GameSession::where('status', 'paused')->count(),
                'active_players' => GamePlayer::where('is_active', true)->count(),
                'suspicious_events' => PositionLog::where('is_suspicious', true)->count(),
                'active_cities' => GameSession::whereIn('status', $ongoingStatuses)->distinct('city_id')->count(),
                'recent_sessions' => GameSession::with(['city', 'host'])
                    ->withCount('players')
                    ->whereIn('status', $ongoingStatuses)
                    ->orderByDesc('updated_at')
                    ->limit(4)
                    ->get()
                    ->map(fn($session) => [
                        'id' => $session->id,
                        'city' => $session->city?->name ?? '—',
                        'host' => $session->host?->name ?? '—',
                        'status' => $session->status,
                        'progress' => $session->total_places > 0 ? round(($session->solved_places / $session->total_places) * 100) : 0,
                        'players_count' => $session->players_count,
                        'remaining_minutes' => max(0, ceil($session->getRemainingSeconds() / 60)),
                        'updated_at' => $session->updated_at?->diffForHumans(),
                        'warning_level' => $session->getTimeWarningLevel(),
                    ])
                    ->toArray(),
            ];
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'gameState' => $gameState,
            // Alias rétro-compatible pour Dashboard / layouts
            'session' => $gameState,
            'adminStats' => $adminStats,
            'cities' => $user ? City::with(['places.riddles'])->withCount('places')->get() : [],
            'flash' => [
                'message' => $request->session()->get('message'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
