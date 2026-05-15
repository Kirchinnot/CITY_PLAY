<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InvitationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $lyon  = City::where('name', 'Découverte du Vieux Lyon')->first();

        // Invitation publique à pied — difficulté moyen
        Invitation::firstOrCreate(
            ['token' => 'lyon-marche-moyen-demo'],
            [
                'city_id'          => $lyon->id,
                'created_by'       => $admin->id,
                'mode'             => 'collectif',
                'difficulty'       => 'moyen',
                'locomotion'       => 'marche',
                'max_players'      => 6,
                'duration_minutes' => 90,
                'expires_at'       => null,
                'used_count'       => 1,
            ]
        );

        // Invitation compétitive en vélo — difficulté difficile
        Invitation::firstOrCreate(
            ['token' => 'lyon-velo-difficile-demo'],
            [
                'city_id'          => $lyon->id,
                'created_by'       => $admin->id,
                'mode'             => 'mercenaire',
                'difficulty'       => 'difficile',
                'locomotion'       => 'velo',
                'max_players'      => 4,
                'duration_minutes' => 60,
                'expires_at'       => now()->addDays(30),
                'used_count'       => 0,
            ]
        );
    }
}
