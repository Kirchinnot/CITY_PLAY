<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\City;
use App\Models\Invitation;
use Illuminate\Support\Str;

class TestGameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. S'assurer qu'un admin existe
        $admin = User::firstOrCreate(
            ['email' => 'admin@cityplay.fr'],
            [
                'name' => 'Admin CityPlay',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]
        );

        // 2. Créer une ville de test
        $city = City::firstOrCreate(
            ['name' => 'Lyon Aventure'],
            [
                'description' => 'Explorez les traboules et les secrets de la capitale des Gaules.',
                'country' => 'France',
                'lat' => 45.7597,
                'lng' => 4.8422,
                'avg_duration_minutes' => 120,
                'is_published' => true,
                'created_by' => $admin->id
            ]
        );

        // 3. Créer une invitation de test
        $invitation = Invitation::firstOrCreate(
            ['token' => 'TEST-TOKEN-CITYPLAY-2026'],
            [
                'city_id' => $city->id,
                'created_by' => $admin->id,
                'mode' => 'collectif',
                'difficulty' => 'moyen',
                'locomotion' => 'marche',
                'duration_minutes' => 90,
                'expires_at' => now()->addDays(30),
            ]
        );

        $this->command->info('Ville de test : ' . $city->name);
        $this->command->info('Lien d\'invitation : ' . route('game.join', ['token' => $invitation->token]));
    }
}
