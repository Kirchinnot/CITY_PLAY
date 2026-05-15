<?php

namespace Database\Seeders;

use App\Models\Environment;
use App\Models\GameSession;
use App\Models\Place;
use App\Models\Riddle;
use App\Models\SessionAnswer;
use App\Models\SessionPlace;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class GameSessionSeeder extends Seeder
{
    public function run(): void
    {
        $env   = Environment::where('city', 'Lyon')->first();
        $team1 = Team::where('name', 'Les Explorateurs')->first();
        $alice = User::where('email', 'alice@cityplay.fr')->first();

        // Récupère les 3 premiers lieux et leurs énigmes force_2
        $places = Place::where('environment_id', $env->id)
                       ->orderBy('visit_order')
                       ->take(3)
                       ->get();

        $firstPlace  = $places->first();
        $firstRiddle = Riddle::where('place_id', $firstPlace->id)
                             ->where('difficulty', 'force_2')
                             ->first();

        // Création d'une session active (unique par user+env)
        $session = GameSession::firstOrCreate(
            ['user_id' => $alice->id, 'environment_id' => $env->id, 'status' => 'active'],
            [
                'team_id'           => $team1->id,
                'current_place_id'  => $firstPlace->id,
                'current_riddle_id' => $firstRiddle->id,
                'transport_mode'    => 'walking',
                'difficulty'        => 'force_2',
                'available_minutes' => 120,
                'score'             => 20,
                'total_riddles'     => 3,
                'solved_riddles'    => 1,
                'failed_riddles'    => 0,
                'started_at'        => now()->subMinutes(30),
            ]
        );

        // Session places
        foreach ($places as $place) {
            $riddle = Riddle::where('place_id', $place->id)
                           ->where('difficulty', 'force_2')
                           ->first();

            SessionPlace::firstOrCreate(
                ['game_session_id' => $session->id, 'place_id' => $place->id],
                [
                    'riddle_id'    => $riddle->id,
                    'is_completed' => $place->visit_order === 1,
                    'attempts_count' => $place->visit_order === 1 ? 1 : 0,
                    'completed_at' => $place->visit_order === 1 ? now()->subMinutes(20) : null,
                    'created_at'   => now()->subMinutes(30),
                ]
            );
        }

        // Historique de réponse pour le premier lieu
        SessionAnswer::firstOrCreate(
            ['game_session_id' => $session->id, 'riddle_id' => $firstRiddle->id, 'attempt_number' => 1],
            [
                'user_answer'           => 'La plus grande place piétonne d\'Europe',
                'is_correct'            => true,
                'response_time_seconds' => 45,
                'latitude'              => 45.75781,
                'longitude'             => 4.83222,
                'distance_from_target'  => 12.5,
                'answered_at'           => now()->subMinutes(20),
                'created_at'            => now()->subMinutes(20),
            ]
        );
    }
}
