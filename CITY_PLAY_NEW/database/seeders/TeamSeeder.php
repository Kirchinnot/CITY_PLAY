<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $alice  = User::where('email', 'alice@cityplay.fr')->first();
        $bob    = User::where('email', 'bob@cityplay.fr')->first();
        $carla  = User::where('email', 'carla@cityplay.fr')->first();
        $david  = User::where('email', 'david@cityplay.fr')->first();

        // Équipe 1 : Alice (capitaine) + Bob
        $team1 = Team::firstOrCreate(
            ['name' => 'Les Explorateurs'],
            ['owner_id' => $alice->id, 'members_numbers' => 2]
        );

        TeamMember::firstOrCreate(
            ['team_id' => $team1->id, 'user_id' => $alice->id],
            ['joined_at' => now()]
        );
        TeamMember::firstOrCreate(
            ['team_id' => $team1->id, 'user_id' => $bob->id],
            ['joined_at' => now()]
        );

        // Équipe 2 : Carla (capitaine) + David
        $team2 = Team::firstOrCreate(
            ['name' => 'Les Aventuriers'],
            ['owner_id' => $carla->id, 'members_numbers' => 2]
        );

        TeamMember::firstOrCreate(
            ['team_id' => $team2->id, 'user_id' => $carla->id],
            ['joined_at' => now()]
        );
        TeamMember::firstOrCreate(
            ['team_id' => $team2->id, 'user_id' => $david->id],
            ['joined_at' => now()]
        );
    }
}
