<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@cityplay.fr'],
            [
                'name'               => 'Admin CityPlay',
                'password'           => Hash::make('password'),
                'phone'              => '+22960000001',
                'role'               => 'admin',
                'two_factor_enabled' => false,
            ]
        );

        $players = [
            ['name' => 'Aïssata Koussoubé', 'email' => 'alice@cityplay.fr', 'phone' => '+22961111111'],
            ['name' => 'Koffi Agbeko',      'email' => 'bob@cityplay.fr',   'phone' => '+22962222222'],
            ['name' => 'Séna Adjou',         'email' => 'carla@cityplay.fr', 'phone' => '+22963333333'],
            ['name' => 'Oumar Dossa',        'email' => 'david@cityplay.fr', 'phone' => '+22964444444'],
        ];

        foreach ($players as $p) {
            User::firstOrCreate(
                ['email' => $p['email']],
                [
                    'name'               => $p['name'],
                    'password'           => Hash::make('password'),
                    'phone'              => $p['phone'],
                    'role'               => 'player',
                    'two_factor_enabled' => false,
                ]
            );
        }
    }
}
