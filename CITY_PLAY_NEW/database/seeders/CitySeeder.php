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
            ['name' => 'Cotonou — Escapade Littorale'],
            [
                'description'          => 'Partez à la découverte du poumon économique du Bénin, entre marché animé, lagune mystérieuse et plages colorées.',
                'country'              => 'Bénin',
                'banner_image'         => null,
                'lat'                  => 6.3703,
                'lng'                  => 2.3912,
                'avg_duration_minutes' => 90,
                'retention_days'       => 365,
                'is_published'         => true,
                'outro_config'         => [
                    'text'           => 'Félicitations ! Tu as révélé les secrets du bord de mer de Cotonou.',
                    'restaurant_tip' => 'Le Jardin du Golfe — Boulevard de la Marina',
                    'shop_url'       => 'https://souvenirs-benin.example.com',
                    'rating_url'     => 'https://cityplay.bj/rate/cotonou',
                ],
                'created_by' => $admin->id,
            ]
        );

        City::firstOrCreate(
            ['name' => 'Ouidah — Route des Ancêtres'],
            [
                'description'          => 'Suivez le chemin historique de la Porte du Non-Retour à l’entrée du royaume d’Abomey, entre culture et mémoire.',
                'country'              => 'Bénin',
                'banner_image'         => null,
                'lat'                  => 6.3606,
                'lng'                  => 2.0875,
                'avg_duration_minutes' => 100,
                'retention_days'       => 365,
                'is_published'         => false,
                'outro_config'         => [
                    'text'           => 'Bravo explorateur ! Tu as honoré les mémoires et découvert la magie d’Ouidah.',
                    'restaurant_tip' => 'Chez Mango — Route de la Plage',
                    'shop_url'       => null,
                    'rating_url'     => 'https://cityplay.bj/rate/ouidah',
                ],
                'created_by' => $admin->id,
            ]
        );
    }
}
