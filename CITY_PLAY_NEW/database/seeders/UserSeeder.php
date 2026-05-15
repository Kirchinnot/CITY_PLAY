<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin (mairie)
        User::firstOrCreate(
            ['email' => 'admin@cityplay.fr'],
            [
                'name'               => 'Admin CityPlay',
                'password'           => Hash::make('password'),
                'role'               => 'admin',
                'two_factor_enabled' => false,
            ]
        );

        // Joueurs de test
        $players = [
            ['name' => 'Alice Martin',  'email' => 'alice@cityplay.fr'],
            ['name' => 'Bob Dupont',    'email' => 'bob@cityplay.fr'],
            ['name' => 'Carla Nguyen',  'email' => 'carla@cityplay.fr'],
            ['name' => 'David Moreau',  'email' => 'david@cityplay.fr'],
        ];

        foreach ($players as $player) {
            User::firstOrCreate(
                ['email' => $player['email']],
                [
                    'name'               => $player['name'],
                    'password'           => Hash::make('password'),
                    'role'               => 'player',
                    'two_factor_enabled' => false,
                ]
            );
        }
    }
}
