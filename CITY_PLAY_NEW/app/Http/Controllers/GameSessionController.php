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
            return redirect()->route('player.dashboard')->with('error', 'Aucune partie trouvée.');
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
        $session->load(['city', 'scores.riddle.place.images', 'achievements']);

        // Calcul du temps total (en secondes)
        $totalTime = 0;
        if ($session->started_at && $session->completed_at) {
            $totalTime = $session->started_at->diffInSeconds($session->completed_at);
        } elseif ($session->started_at) {
            $totalTime = $session->started_at->diffInSeconds(now());
        }

        // Trouver les lieux non résolus (sélectionnés pour la session, mais non complétés)
        $selectedPlacesIds = collect($session->settings['selected_places'] ?? []);
        $solvedPlacesIds = collect($session->settings['solved_places_ids'] ?? []);
        $unsolvedPlacesIds = $selectedPlacesIds->diff($solvedPlacesIds);

        $unsolvedPlaces = \App\Models\Place::with('images')
            ->whereIn('id', $unsolvedPlacesIds)
            ->get()
            ->map(function($place) {
                return [
                    'id' => $place->id,
                    'name' => $place->name,
                    'description' => $place->description,
                    'latitude' => $place->latitude,
                    'longitude' => $place->longitude,
                    'images' => $place->images->map(function($img) {
                        return ['path' => $img->path];
                    }),
                ];
            });
        
        return Inertia::render('Gameplay/Summary', [
            'session' => [
                'id' => $session->id,
                'city_name' => $session->city?->name ?? 'Ville inconnue',
                'outro_config' => $session->city?->outro_config,
                'total_score' => $session->scores->sum('points_earned'),
                'total_time' => $totalTime,
                'places_discovered' => $session->solved_places,
                'total_places' => $session->total_places,
                'difficulty' => $session->difficulty,
                'mode' => $session->mode,
                'unsolved_places' => $unsolvedPlaces,
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
     * Met la session en pause.
     */
    public function pause(Request $request, GameSession $session)
    {
        $user = $request->user();

        // Seul le host peut mettre en pause
        if ($session->host_user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if (!$session->isActive()) {
            return response()->json(['message' => 'La session n\'est pas active.'], 422);
        }

        $session->update([
            'status'    => 'paused',
            'paused_at' => now(),
        ]);

        return response()->json(['status' => 'paused', 'paused_at' => $session->paused_at]);
    }

    /**
     * Reprend une session en pause.
     */
    public function resume(Request $request, GameSession $session)
    {
        $user = $request->user();

        if ($session->host_user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if (!$session->isPaused()) {
            return response()->json(['message' => 'La session n\'est pas en pause.'], 422);
        }

        // Cumule le temps de pause
        $pauseSeconds = $session->paused_at
            ? (int) $session->paused_at->diffInSeconds(now())
            : 0;

        $session->update([
            'status'               => 'active',
            'paused_at'            => null,
            'total_pause_seconds'  => $session->total_pause_seconds + $pauseSeconds,
        ]);

        return response()->json(['status' => 'active', 'total_pause_seconds' => $session->total_pause_seconds]);
    }

    /**
     * Abandonne une session.
     */
    public function abandon(Request $request, GameSession $session)
    {
        $user = $request->user();

        if ($session->host_user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($session->isFinished()) {
            return response()->json(['message' => 'La session est déjà terminée.'], 422);
        }

        $session->update([
            'status'       => 'abandoned',
            'completed_at' => now(),
        ]);

        return response()->json(['status' => 'abandoned']);
    }

    /**
     * Démarre une nouvelle session de jeu pour une ville donnée.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'city_id'          => 'required|exists:cities,id',
            'team_members'     => 'required|integer|min:1|max:9',
            'duration_minutes' => 'required|integer|min:20|max:480',
            'locomotion'       => 'required|in:marche,velo,voiture',
            'difficulty'       => 'required|in:force_1,force_2,force_3',
        ]);

        $user = $request->user();
        $city = City::with(['places' => function ($query) {
            $query->orderBy('order_index');
        }])->findOrFail($validated['city_id']);

        // Vérifier si une session active existe déjà
        $activeSession = GameSession::whereHas('gamePlayers', function($query) use ($user) {
                $query->where('user_id', $user->id)->where('is_active', true);
            })
            ->where('status', 'active')
            ->first();

        if ($activeSession) {
            return redirect()->route('player.dashboard')->with('error', 'Vous avez déjà une partie en cours.');
        }

        if ($city->places->count() === 0) {
            return redirect()->route('player.dashboard')->with('error', 'Cette ville n\'a pas encore de lieux configurés.');
        }

        // 1. Calcul du nombre d'énigmes en fonction de la locomotion et de la durée
        $factor = 30; // par défaut pieds / marche (30 min par étape)
        if ($validated['locomotion'] === 'velo') {
            $factor = 15; // velo (15 min par étape)
        } elseif ($validated['locomotion'] === 'voiture') {
            $factor = 10; // voiture (10 min par étape)
        }

        $calculatedCount = max(1, (int) floor($validated['duration_minutes'] / $factor));
        $calculatedCount = min($calculatedCount, $city->places->count());

        // Choisir les énigmes/lieux en fonction du classement effectué par la mairie (order_index)
        $selectedPlaces = $city->places->take($calculatedCount);

        return DB::transaction(function () use ($user, $city, $validated, $selectedPlaces) {
            // Création de l'invitation liée à cette session de jeu personnalisée
            $invitation = Invitation::create([
                'city_id'          => $city->id,
                'created_by'       => $user->id,
                'token'            => (string) Str::uuid(),
                'mode'             => 'collectif',
                'difficulty'       => $validated['difficulty'],
                'locomotion'       => $validated['locomotion'],
                'max_players'      => 10,
                'duration_minutes' => $validated['duration_minutes'],
            ]);

            // 2. Création de la session
            $session = GameSession::create([
                'invitation_id'       => $invitation->id,
                'city_id'             => $city->id,
                'host_user_id'        => $user->id,
                'mode'                => 'collectif',
                'difficulty'          => $validated['difficulty'],
                'locomotion'          => $validated['locomotion'],
                'available_minutes'   => $validated['duration_minutes'],
                'status'              => 'active',
                'total_places'        => $selectedPlaces->count(),
                'current_place_index' => 0,
                'started_at'          => now(),
            ]);

            // 3. Ajout du joueur (Chef de l'équipe)
            GamePlayer::create([
                'game_session_id' => $session->id,
                'user_id'         => $user->id,
                'joined_at'       => now(),
                'is_active'       => true,
            ]);

            // 4. Initialisation des lieux de la session filtrés et ordonnés
            foreach ($selectedPlaces as $index => $place) {
                SessionPlace::create([
                    'game_session_id' => $session->id,
                    'place_id'        => $place->id,
                    'order_index'     => $index,
                    'is_completed'    => false,
                ]);
            }

            return redirect()->route('player.dashboard')->with('success', 'Partie démarrée avec ' . $selectedPlaces->count() . ' énigmes calculées pour votre parcours !');
        });
    }
}
