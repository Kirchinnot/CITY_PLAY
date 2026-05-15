<?php

namespace Database\Seeders;

use App\Models\Environment;
use App\Models\Place;
use App\Models\PlaceImage;
use Illuminate\Database\Seeder;

class PlaceSeeder extends Seeder
{
    public function run(): void
    {
        $env = Environment::where('city', 'Lyon')->first();

        $places = [
            [
                'name'                    => 'Place Bellecour',
                'description'             => 'La plus grande place piétonne d\'Europe et le cœur battant de Lyon, dominée par la statue équestre de Louis XIV.',
                'latitude'                => 45.75781,
                'longitude'               => 4.83222,
                'validation_radius'       => 50,
                'estimated_visit_minutes' => 15,
                'visit_order'             => 1,
                'images' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b8/Lyon_place_bellecour_2019.jpg/1280px-Lyon_place_bellecour_2019.jpg',
                ],
            ],
            [
                'name'                    => 'Basilique de Fourvière',
                'description'             => 'Juchée sur la colline qui prie, la basilique Notre-Dame de Fourvière offre une vue panoramique exceptionnelle sur Lyon.',
                'latitude'                => 45.76228,
                'longitude'               => 4.82223,
                'validation_radius'       => 30,
                'estimated_visit_minutes' => 20,
                'visit_order'             => 2,
                'images' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3e/Lyon_-_Basilique_Notre-Dame_de_Fourvière_%28vue_de_nuit%29.jpg/1280px-Lyon_-_Basilique_Notre-Dame_de_Fourvière_%28vue_de_nuit%29.jpg',
                ],
            ],
            [
                'name'                    => 'Traboules du Vieux Lyon',
                'description'             => 'Ces passages couverts traversant les immeubles de la Renaissance sont l\'âme secrète du Vieux Lyon.',
                'latitude'                => 45.76305,
                'longitude'               => 4.82689,
                'validation_radius'       => 40,
                'estimated_visit_minutes' => 20,
                'visit_order'             => 3,
                'images' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/Traboule_-_Lyon.jpg/800px-Traboule_-_Lyon.jpg',
                ],
            ],
            [
                'name'                    => 'Musée des Beaux-Arts de Lyon',
                'description'             => 'Installé dans un ancien couvent bénédictin, ce musée abrite l\'une des plus riches collections d\'art de France.',
                'latitude'                => 45.76749,
                'longitude'               => 4.83365,
                'validation_radius'       => 30,
                'estimated_visit_minutes' => 30,
                'visit_order'             => 4,
                'images' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/3/35/Lyon_Musee_des_Beaux_Arts_facade.jpg/1280px-Lyon_Musee_des_Beaux_Arts_facade.jpg',
                ],
            ],
            [
                'name'                    => 'Institut Lumière',
                'description'             => 'Berceau du cinéma mondial, c\'est ici que les frères Lumière ont inventé le cinématographe en 1895.',
                'latitude'                => 45.74788,
                'longitude'               => 4.85444,
                'validation_radius'       => 30,
                'estimated_visit_minutes' => 25,
                'visit_order'             => 5,
                'images' => [
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/Institut_Lumi%C3%A8re.jpg/800px-Institut_Lumi%C3%A8re.jpg',
                ],
            ],
        ];

        foreach ($places as $data) {
            $images = $data['images'];
            unset($data['images']);

            $place = Place::firstOrCreate(
                ['environment_id' => $env->id, 'name' => $data['name']],
                array_merge($data, ['environment_id' => $env->id])
            );

            foreach ($images as $order => $url) {
                PlaceImage::firstOrCreate(
                    ['place_id' => $place->id, 'image_url' => $url],
                    ['display_order' => $order + 1, 'created_at' => now()]
                );
            }
        }
    }
}
