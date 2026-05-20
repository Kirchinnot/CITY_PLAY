<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Place;
use App\Models\PlaceImage;
use Illuminate\Database\Seeder;

class PlaceSeeder extends Seeder
{
    public function run(): void
    {
        $city = City::where('name', 'Cotonou — Escapade Littorale')->first();

        $places = [
            [
                'name'               => 'Marché Dantokpa',
                'description'        => 'Le plus grand marché d\'Afrique de l\'Ouest, un labyrinthe de couleurs, d\'épices et de vie béninoise.',
                'lat'                => 6.3698,
                'lng'                => 2.4226,
                'validation_radius'  => 60,
                'order_index'        => 1,
                'estimated_time_min' => 20,
                'images' => [
                    ['url' => 'https://upload.wikimedia.org/wikipedia/commons/7/73/Dantokpa_market.jpg', 'caption' => 'Allée du Marché Dantokpa'],
                ],
            ],
            [
                'name'               => 'Plage des Cocotiers',
                'description'        => 'Une plage urbaine animée idéale pour sentir l\'ambiance du Golfe de Guinée et observer les pêcheurs locaux.',
                'lat'                => 6.3533,
                'lng'                => 2.4327,
                'validation_radius'  => 70,
                'order_index'        => 2,
                'estimated_time_min' => 15,
                'images' => [
                    ['url' => 'https://upload.wikimedia.org/wikipedia/commons/2/2a/Plage_Cotonou.jpg', 'caption' => 'Plage du bord de mer'],
                ],
            ],
            [
                'name'               => 'Musée Historique de Ouidah',
                'description'        => 'Un musée chargé d\'histoire où s\'inscrivent les récits du royaume d\'Abomey et du commerce transatlantique.',
                'lat'                => 6.3651,
                'lng'                => 2.0904,
                'validation_radius'  => 50,
                'order_index'        => 3,
                'estimated_time_min' => 25,
                'images' => [
                    ['url' => 'https://upload.wikimedia.org/wikipedia/commons/5/56/Ouidah_museum.jpg', 'caption' => 'Musée historique de Ouidah'],
                ],
            ],
            [
                'name'               => 'Porte du Non-Retour',
                'description'        => 'Monument mémoriel sur la Route des Esclaves, symbole de mémoire et de résilience.',
                'lat'                => 6.3648,
                'lng'                => 2.0873,
                'validation_radius'  => 40,
                'order_index'        => 4,
                'estimated_time_min' => 20,
                'images' => [
                    ['url' => 'https://upload.wikimedia.org/wikipedia/commons/5/59/Port_of_No_Return_Ouidah.jpg', 'caption' => 'La Porte du Non-Retour'],
                ],
            ],
            [
                'name'               => 'Palais Royal d\'Abomey',
                'description'        => 'Le cœur historique du royaume dahoméen, avec ses palais et ses bas-reliefs légendaires.',
                'lat'                => 7.1802,
                'lng'                => 2.4162,
                'validation_radius'  => 50,
                'order_index'        => 5,
                'estimated_time_min' => 30,
                'images' => [
                    ['url' => 'https://upload.wikimedia.org/wikipedia/commons/8/8a/Abomey_palace.jpg', 'caption' => 'Entrée du Palais Royal d\'Abomey'],
                ],
            ],
        ];

        foreach ($places as $data) {
            $images = $data['images'];
            unset($data['images']);

            $place = Place::firstOrCreate(
                ['city_id' => $city->id, 'name' => $data['name']],
                array_merge($data, ['city_id' => $city->id])
            );

            foreach ($images as $order => $img) {
                PlaceImage::firstOrCreate(
                    ['place_id' => $place->id, 'image_url' => $img['url']],
                    ['display_order' => $order + 1, 'caption' => $img['caption'], 'created_at' => now()]
                );
            }
        }
    }
}
