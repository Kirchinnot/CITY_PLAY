<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        City::firstOrCreate(
            ['name' => 'Découverte du Vieux Lyon'],
            [
                'description'          => 'Partez à la découverte des trésors cachés du Vieux Lyon, de la Place Bellecour aux traboules de la Renaissance.',
                'country'              => 'France',
                'banner_image'         => null,
                'lat'                  => 45.7597,
                'lng'                  => 4.8422,
                'avg_duration_minutes' => 90,
                'retention_days'       => 365,
                'is_published'         => true,
                'outro_config'         => [
                    'text'           => 'Bravo pour votre parcours ! Vous avez exploré les joyaux de Lyon.',
                    'restaurant_tip' => 'Bouchon Lyonnais — 2 rue Mercière, Lyon 2e',
                    'shop_url'       => 'https://souvenirs-lyon.fr',
                    'rating_url'     => 'https://cityplay.fr/rate/lyon',
                ],
                'created_by' => $admin->id,
            ]
        );

        City::firstOrCreate(
            ['name' => 'Secrets de Montmartre'],
            [
                'description'          => 'Explorez les ruelles artistiques et les anecdotes historiques de la Butte Montmartre.',
                'country'              => 'France',
                'banner_image'         => null,
                'lat'                  => 48.8867,
                'lng'                  => 2.3431,
                'avg_duration_minutes' => 75,
                'retention_days'       => 180,
                'is_published'         => false,
                'outro_config'         => [
                    'text'           => 'Félicitations ! Terminez votre visite au café des Deux Moulins.',
                    'restaurant_tip' => 'Café des Deux Moulins — 15 rue Lepic, Paris 18e',
                    'shop_url'       => null,
                    'rating_url'     => 'https://cityplay.fr/rate/montmartre',
                ],
                'created_by' => $admin->id,
            ]
        );
    }
}
