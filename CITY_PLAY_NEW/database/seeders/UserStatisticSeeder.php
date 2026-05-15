<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserStatistic;
use Illuminate\Database\Seeder;

class UserStatisticSeeder extends Seeder
{
    public function run(): void
    {
        $stats = [
            [
                'email'                 => 'alice@cityplay.fr',
                'total_games'           => 3,
                'total_score'           => 180,
                'total_riddles_solved'  => 12,
                'total_riddles_failed'  => 2,
                'total_distance_walked' => 4.5,
                'average_response_time' => 38.2,
                'success_rate'          => 85.7,
            ],
            [
                'email'                 => 'bob@cityplay.fr',
                'total_games'           => 2,
                'total_score'           => 110,
                'total_riddles_solved'  => 8,
                'total_riddles_failed'  => 3,
                'total_distance_walked' => 3.1,
                'average_response_time' => 52.0,
                'success_rate'          => 72.7,
            ],
            [
                'email'                 => 'carla@cityplay.fr',
                'total_games'           => 1,
                'total_score'           => 60,
                'total_riddles_solved'  => 5,
                'total_riddles_failed'  => 1,
                'total_distance_walked' => 2.3,
                'average_response_time' => 44.5,
                'success_rate'          => 83.3,
            ],
            [
                'email'                 => 'david@cityplay.fr',
                'total_games'           => 1,
                'total_score'           => 45,
                'total_riddles_solved'  => 4,
                'total_riddles_failed'  => 2,
                'total_distance_walked' => 1.8,
                'average_response_time' => 61.0,
                'success_rate'          => 66.7,
            ],
        ];

        foreach ($stats as $data) {
            $email = $data['email'];
            unset($data['email']);

            $user = User::where('email', $email)->first();
            if ($user) {
                UserStatistic::firstOrCreate(
                    ['user_id' => $user->id],
                    $data
                );
            }
        }
    }
}
