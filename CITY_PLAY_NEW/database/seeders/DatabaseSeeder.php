<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Ordre strict : chaque seeder dépend des tables peuplées par les précédents.
     *
     * users → cities → places → riddles (+ hints) → invitations
     *       → game_sessions → game_players → session_places → scores → achievements
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,        // 1. Comptes admin + joueurs
            CitySeeder::class,        // 2. Villes/aventures (FK users)
            PlaceSeeder::class,       // 3. Lieux + images (FK cities)
            RiddleSeeder::class,      // 4. Énigmes + indices (FK places)
            InvitationSeeder::class,  // 5. Invitations (FK cities + users)
            GameSessionSeeder::class, // 6. Sessions + joueurs + session_places + scores + achievements
        ]);
    }
}
