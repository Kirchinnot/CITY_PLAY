<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\SessionPlace;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GameSessionController extends Controller
{
    /**
     * Affiche la carte du jeu avec la position du joueur et les POI.
     */
    public function map(Request $request)
    {
        $user = $request->user();
        
        // Si c'est un admin, on prend la dernière session active globale ou la sienne
        if ($user->role === 'admin') {
            $session = GameSession::whereIn('status', ['active', 'completed'])
                ->with(['city', 'sessionPlaces.place'])
                ->latest()
                ->first();
        } else {
            // Pour un joueur, sa session active ou sa dernière session terminée
            $session = GameSession::whereHas('gamePlayers', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->whereIn('status', ['active', 'completed'])
                ->with(['city', 'sessionPlaces.place'])
                ->latest()
                ->first();
        }

        if (!$session) {
            return redirect()->route('dashboard')->with('error', 'Aucune partie trouvée.');
        }

        return Inertia::render('Gameplay/Map', [
            'session' => $session,
        ]);
    }

    /**
     * Affiche le bilan d'une session terminée.
     */
    public function summary(GameSession $session)
    {
        $session->load(['city', 'scores.riddle', 'achievements']);

        // Calcul du temps total (en secondes)
        $totalTime = 0;
        if ($session->started_at && $session->completed_at) {
            $totalTime = $session->started_at->diffInSeconds($session->completed_at);
        } elseif ($session->started_at) {
            $totalTime = $session->started_at->diffInSeconds(now());
        }
        
        return Inertia::render('Gameplay/Summary', [
            'session' => [
                'id' => $session->id,
                'city_name' => $session->city?->name ?? 'Ville inconnue',
                'total_score' => $session->scores->sum('points_earned'),
                'total_time' => $totalTime,
                'places_discovered' => $session->solved_places,
                'total_places' => $session->total_places,
                'difficulty' => $session->difficulty,
                'mode' => $session->mode,
                'scores' => $session->scores->map(function ($score) {
                    return [
                        'id' => $score->id,
                        'points_earned' => $score->points_earned,
                        'hints_used' => $score->hints_used,
                        'time_taken_seconds' => $score->time_taken_seconds,
                        'riddle' => $score->riddle ? [
                            'id' => $score->riddle->id,
                            'title' => $score->riddle->title,
                        ] : null,
                    ];
                }),
                'achievements' => $session->achievements->map(function ($achievement) {
                    // Mapping des types vers des labels et icônes lisibles
                    $labels = [
                        'explorateur' => ['name' => 'Explorateur', 'icon' => '🗺️'],
                        'rapide'      => ['name' => 'Éclair', 'icon' => '⚡'],
                        'sans_indice' => ['name' => 'Sans Indice', 'icon' => '🧠'],
                    ];
                    $info = $labels[$achievement->type] ?? ['name' => ucfirst($achievement->type), 'icon' => '🏆'];
                    return [
                        'id'   => $achievement->id,
                        'type' => $achievement->type,
                        'name' => $info['name'],
                        'icon' => $info['icon'],
                    ];
                }),
            ]
        ]);
    }

    /**
     * Démarre une nouvelle session de jeu pour une ville donnée.
     */
    public function store(Request $request)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
        ]);

        $user = $request->user();
        $city = City::with('places')->findOrFail($request->city_id);

        // Vérifier si une session active existe déjà
        $activeSession = GameSession::whereHas('gamePlayers', function($query) use ($user) {
                $query->where('user_id', $user->id)->where('is_active', true);
            })
            ->where('status', 'active')
            ->first();

        if ($activeSession) {
            return redirect()->route('dashboard')->with('error', 'Vous avez déjà une partie en cours.');
        }

        return DB::transaction(function () use ($user, $city) {
            // 1. Trouver ou créer une invitation par défaut pour cette ville
            $invitation = Invitation::firstOrCreate(
                ['city_id' => $city->id, 'created_by' => $user->id],
                [
                    'token' => (string) Str::uuid(),
                    'mode' => 'collectif',
                    'difficulty' => 'force_1',
                    'locomotion' => 'marche',
                    'max_players' => 10,
                    'duration_minutes' => $city->avg_duration_minutes ?? 120,
                ]
            );

            $totalPlaces = $city->places->count();

            if ($totalPlaces === 0) {
                return redirect()->route('dashboard')->with('error', 'Cette ville n\'a pas encore de lieux configurés.');
            }

            // 2. Création de la session
            $session = GameSession::create([
                'invitation_id' => $invitation->id,
                'city_id' => $city->id,
                'host_user_id' => $user->id,
                'mode' => $invitation->mode,
                'difficulty' => $invitation->difficulty,
                'locomotion' => $invitation->locomotion,
                'available_minutes' => $invitation->duration_minutes,
                'status' => 'active',
                'total_places' => $totalPlaces,
                'current_place_index' => 0,
                'started_at' => now(),
            ]);

            // 3. Ajout du joueur
            GamePlayer::create([
                'game_session_id' => $session->id,
                'user_id' => $user->id,
                'joined_at' => now(),
                'is_active' => true,
            ]);

            // 4. Initialisation des lieux de la session
            foreach ($city->places as $index => $place) {
                SessionPlace::create([
                    'game_session_id' => $session->id,
                    'place_id' => $place->id,
                    'order_index' => $index,
                    'is_completed' => false,
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Partie démarrée !');
        });
    }
}
