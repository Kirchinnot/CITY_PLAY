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
            'session' => tap($session, function($s) {
                // Passer l'énigme courante pour le bouton "Jouer" sur la carte
                if ($s && $s->status === 'active') {
                    $currentPlace = $s->sessionPlaces()
                        ->where('order_index', $s->current_place_index)
                        ->first();
                    if ($currentPlace) {
                        $solvedIds = \App\Models\Score::where('game_session_id', $s->id)->pluck('riddle_id')->toArray();
                        $riddle = \App\Models\Riddle::where('place_id', $currentPlace->place_id)
                            ->where('difficulty', $s->difficulty)
                            ->whereNotIn('id', $solvedIds)
                            ->get()
                            ->sortBy(function($r) use ($s) {
                                return md5($r->id . '_' . $s->id);
                            })
                            ->first();
                        $s->current_riddle = $riddle;
                    }
                }
            }),
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
        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'difficulty' => 'nullable|in:facile,moyen,difficile',
            'mode' => 'nullable|in:solo,collectif,mercenaire',
            'start_place_id' => 'nullable|exists:places,id',
        ]);

        $user = $request->user();
        $city = City::with('places')->findOrFail($request->city_id);
        $difficulty = $request->input('difficulty', 'moyen');
        $inputMode = $request->input('mode', 'collectif');
        $mode = $inputMode === 'solo' ? 'mercenaire' : $inputMode;
        $startPlaceId = $request->input('start_place_id');

        // Vérifier si une session active existe déjà
        $activeSession = GameSession::whereHas('gamePlayers', function($query) use ($user) {
                $query->where('user_id', $user->id)->where('is_active', true);
            })
            ->where('status', 'active')
            ->first();

        if ($activeSession) {
            return redirect()->route('player.dashboard')->with('error', 'Vous avez déjà une partie en cours.');
        }

        return DB::transaction(function () use ($user, $city, $difficulty, $mode, $startPlaceId) {
            // 1. Créer une invitation personnalisée pour cette ville
            $invitation = Invitation::create([
                'city_id' => $city->id,
                'created_by' => $user->id,
                'token' => (string) Str::uuid(),
                'mode' => $mode,
                'difficulty' => $difficulty,
                'locomotion' => 'marche',
                'max_players' => 10,
                'duration_minutes' => $city->avg_duration_minutes ?? 120,
            ]);

            // Filtre les lieux pour ne garder que ceux qui possèdent au moins une énigme de la difficulté choisie
            $placesWithDifficulty = $city->places->filter(function($place) use ($difficulty) {
                return \App\Models\Riddle::where('place_id', $place->id)
                    ->where('difficulty', $difficulty)
                    ->exists();
            });

            if ($placesWithDifficulty->isEmpty()) {
                return redirect()->route('player.dashboard')->with('error', 'Cette ville n\'a pas de lieux configurés pour la difficulté choisie.');
            }

            // Déterminer le lieu unique pour cette session de jeu
            $selectedPlace = null;
            if ($startPlaceId) {
                $selectedPlace = $placesWithDifficulty->firstWhere('id', $startPlaceId);
            } else {
                // Fallback si aucun lieu n'est explicitement cliqué : on prend le premier disponible
                $selectedPlace = $placesWithDifficulty->shuffle()->first();
            }

            if (!$selectedPlace) {
                return redirect()->route('player.dashboard')->with('error', 'Le lieu choisi n\'est pas disponible pour cette difficulté.');
            }

            // Une session de jeu est maintenant restreinte à un SEUL lieu (succession d'énigmes liée à ce lieu)
            $totalPlaces = 1;
            $startIndex = 0;

            // 2. Création de la session
            $session = GameSession::create([
                'invitation_id' => $invitation->id,
                'city_id' => $city->id,
                'host_user_id' => $user->id,
                'mode' => $invitation->mode,
                'difficulty' => $invitation->difficulty,
                'locomotion' => $invitation->locomotion,
                'available_minutes' => $invitation->duration_minutes,
                'status' => $mode === 'collectif' ? 'waiting' : 'active',
                'total_places' => $totalPlaces,
                'current_place_index' => $startIndex,
                'started_at' => now(),
            ]);

            // 3. Ajout du joueur (Chef de l'équipe)
            GamePlayer::create([
                'game_session_id' => $session->id,
                'user_id'         => $user->id,
                'joined_at'       => now(),
                'is_active'       => true,
            ]);

            // 4. Initialisation du lieu unique de la session
            SessionPlace::create([
                'game_session_id' => $session->id,
                'place_id' => $selectedPlace->id,
                'order_index' => 0,
                'is_completed' => false,
            ]);

            // Redirection directe vers la première énigme de ce lieu
            $riddle = \App\Models\Riddle::where('place_id', $selectedPlace->id)
                ->where('difficulty', $session->difficulty)
                ->get()
                ->sortBy(function($r) use ($session) {
                    return md5($r->id . '_' . $session->id);
                })
                ->first();
            
            if ($riddle) {
                if ($mode === 'collectif') {
                    return redirect()->route('game.lobby', $session->id);
                }
                return redirect()->route('player.riddle.show', $riddle->id)->with('success', 'L\'aventure commence !');
            }

            if ($mode === 'collectif') {
                return redirect()->route('game.lobby', $session->id);
            }
            return redirect()->route('player.dashboard')->with('success', 'Partie démarrée avec ' . $selectedPlaces->count() . ' énigmes calculées pour votre parcours !');
        });
    }

    /**
     * Sélectionne librement un lieu sur la carte.
     */
    public function selectPlace(Request $request, GameSession $session)
    {
        $request->validate([
            'place_id' => 'required|exists:places,id',
        ]);

        $user = $request->user();

        // Trouver la SessionPlace correspondante
        $sessionPlace = $session->sessionPlaces()
            ->where('place_id', $request->place_id)
            ->firstOrFail();

        if ($sessionPlace->is_completed) {
            return response()->json(['message' => 'Ce lieu a déjà été complété.'], 422);
        }

        // Mettre à jour l'index actuel de la session pour correspondre à l'index de ce lieu
        $session->update([
            'current_place_index' => $sessionPlace->order_index,
        ]);

        // Trouver la première énigme non résolue de ce lieu selon la difficulté de la session, mélangée de manière stable
        $solvedRiddleIds = \App\Models\Score::where('game_session_id', $session->id)->pluck('riddle_id')->toArray();
        $riddle = \App\Models\Riddle::where('place_id', $request->place_id)
            ->where('difficulty', $session->difficulty)
            ->whereNotIn('id', $solvedRiddleIds)
            ->get()
            ->sortBy(function($r) use ($session) {
                return md5($r->id . '_' . $session->id);
            })
            ->first();

        return response()->json([
            'status' => 'success',
            'current_place_index' => $session->current_place_index,
            'next_riddle_id' => $riddle ? $riddle->id : null,
        ]);
    }

    /**
     * Rejoindre une session de jeu via un lien d'invitation (token).
     */
    public function join(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();
        $session = GameSession::where('invitation_id', $invitation->id)->latest()->firstOrFail();
        $user = $request->user();

        // Si le joueur n'est pas déjà dans la partie, on l'ajoute
        $exists = GamePlayer::where('game_session_id', $session->id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$exists) {
            GamePlayer::create([
                'game_session_id' => $session->id,
                'user_id' => $user->id,
                'joined_at' => now(),
                'is_active' => true,
            ]);

            // Broadcast de l'événement pour mettre à jour le lobby en temps réel
            if (class_exists(\App\Events\PlayerJoined::class)) {
                broadcast(new \App\Events\PlayerJoined($session->id, $user))->toOthers();
            }
        }

        if ($session->status === 'active') {
            return redirect()->route('player.dashboard')->with('success', 'Vous avez rejoint la partie en cours !');
        }

        return redirect()->route('game.lobby', $session->id);
    }

    /**
     * Affiche le salon d'attente (Lobby) avant le début de la partie.
     */
    public function lobby(Request $request, GameSession $session)
    {
        $session->load(['city', 'invitation', 'gamePlayers.user']);

        return Inertia::render('Game/Lobby', [
            'session' => [
                'id' => $session->id,
                'status' => $session->status,
                'mode' => $session->mode,
                'difficulty' => $session->difficulty,
                'locomotion' => $session->locomotion,
                'available_minutes' => $session->available_minutes,
                'host_user_id' => $session->host_user_id,
                'city' => $session->city,
                'invitation' => $session->invitation,
                'players' => $session->gamePlayers->map(function ($gp) {
                    return $gp->user;
                }),
            ],
            'currentUser' => $request->user(),
            'invitationUrl' => route('game.join', ['token' => $session->invitation->token]),
        ]);
    }

    /**
     * Le chef de clan lance la partie depuis le lobby.
     */
    public function start(Request $request, GameSession $session)
    {
        $user = $request->user();

        if ($session->host_user_id !== $user->id) {
            return redirect()->back()->with('error', 'Seul le chef de clan peut démarrer la partie.');
        }

        $session->update([
            'status' => 'active',
            'started_at' => now()
        ]);

        // Récupération de la première énigme
        $currentPlace = $session->sessionPlaces()->where('order_index', 0)->first();
        if ($currentPlace) {
            $riddle = \App\Models\Riddle::where('place_id', $currentPlace->place_id)
                ->where('difficulty', $session->difficulty)
                ->get()
                ->sortBy(function($r) use ($session) {
                    return md5($r->id . '_' . $session->id);
                })
                ->first();
                
            if ($riddle) {
                return redirect()->route('player.riddle.show', $riddle->id)->with('success', 'L\'aventure commence !');
            }
        }

        return redirect()->route('player.dashboard')->with('success', 'L\'aventure commence !');
    }
}
