<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * L'ordre est important : respecter les dépendances entre tables (FK).
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,          // 1. Utilisateurs (admin + joueurs)
            TeamSeeder::class,          // 2. Équipes + membres
            EnvironmentSeeder::class,   // 3. Environnements (parcours)
            PlaceSeeder::class,         // 4. Lieux + images des lieux
            RiddleSeeder::class,        // 5. Énigmes (4 niveaux) + réponses
            GameSessionSeeder::class,   // 6. Session de jeu de démo
            UserStatisticSeeder::class, // 7. Statistiques des joueurs
        ]);
    }
}
