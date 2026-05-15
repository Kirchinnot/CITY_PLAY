<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\City;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Invitation;
use App\Models\Place;
use App\Models\Riddle;
use App\Models\Score;
use App\Models\SessionPlace;
use App\Models\User;
use Illuminate\Database\Seeder;

class GameSessionSeeder extends Seeder
{
    public function run(): void
    {
        $alice      = User::where('email', 'alice@cityplay.fr')->first();
        $bob        = User::where('email', 'bob@cityplay.fr')->first();
        $lyon       = City::where('name', 'Découverte du Vieux Lyon')->first();
        $invitation = Invitation::where('token', 'lyon-marche-moyen-demo')->first();

        // ── Session active (Alice est hôte, Bob est joueur) ─────────────────
        $session = GameSession::firstOrCreate(
            ['invitation_id' => $invitation->id, 'host_user_id' => $alice->id],
            [
                'city_id'             => $lyon->id,
                'mode'                => 'collectif',
                'difficulty'          => 'moyen',
                'locomotion'          => 'marche',
                'available_minutes'   => 90,
                'status'              => 'active',
                'current_place_index' => 1,
                'total_places'        => 3,
                'solved_places'       => 1,
                'started_at'          => now()->subMinutes(25),
                'total_pause_seconds' => 0,
            ]
        );

        // ── Joueurs ──────────────────────────────────────────────────────────
        GamePlayer::firstOrCreate(
            ['game_session_id' => $session->id, 'user_id' => $alice->id],
            ['joined_at' => now()->subMinutes(30), 'is_active' => true, 'last_lat' => 45.7578, 'last_lng' => 4.8322, 'last_seen_at' => now()->subMinutes(2)]
        );
        GamePlayer::firstOrCreate(
            ['game_session_id' => $session->id, 'user_id' => $bob->id],
            ['joined_at' => now()->subMinutes(28), 'is_active' => true, 'last_lat' => 45.7578, 'last_lng' => 4.8322, 'last_seen_at' => now()->subMinutes(1)]
        );

        // ── 3 lieux sélectionnés pour cette session ──────────────────────────
        $places = Place::where('city_id', $lyon->id)->orderBy('order_index')->take(3)->get();
        foreach ($places as $place) {
            SessionPlace::firstOrCreate(
                ['game_session_id' => $session->id, 'place_id' => $place->id],
                [
                    'order_index'  => $place->order_index,
                    'is_completed' => $place->order_index === 1,
                    'completed_at' => $place->order_index === 1 ? now()->subMinutes(15) : null,
                ]
            );
        }

        // ── Score pour le 1er lieu résolu ────────────────────────────────────
        $firstPlace  = $places->first();
        $firstRiddle = Riddle::where('place_id', $firstPlace->id)->where('difficulty', 'moyen')->first();

        if ($firstRiddle) {
            Score::firstOrCreate(
                ['game_session_id' => $session->id, 'riddle_id' => $firstRiddle->id, 'user_id' => null],
                [
                    'points_earned'      => 145,
                    'points_speed'       => 80,
                    'points_distance'    => 50,
                    'points_hints_bonus' => 15,
                    'points_difficulty'  => 0,
                    'hints_used'         => 1,
                    'time_taken_seconds' => 42,
                    'distance_m'         => 18.5,
                    'is_blocked'         => false,
                    'resolved_at'        => now()->subMinutes(15),
                ]
            );

            // ── Badge décroché par Alice ─────────────────────────────────────
            Achievement::firstOrCreate(
                ['user_id' => $alice->id, 'game_session_id' => $session->id, 'type' => 'rapide'],
                ['earned_at' => now()->subMinutes(15)]
            );
        }
    }
}
