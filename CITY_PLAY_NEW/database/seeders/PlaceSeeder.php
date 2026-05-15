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
        $city = City::where('name', 'Découverte du Vieux Lyon')->first();

        $places = [
            [
                'name'               => 'Place Bellecour',
                'description'        => 'La plus grande place piétonne d\'Europe, cœur battant de Lyon, dominée par la statue équestre de Louis XIV.',
                'lat'                => 45.7578,
                'lng'                => 4.8322,
                'validation_radius'  => 50,
                'order_index'        => 1,
                'estimated_time_min' => 10,
                'images' => [
                    ['url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b8/Lyon_place_bellecour_2019.jpg/1280px-Lyon_place_bellecour_2019.jpg', 'caption' => 'Vue générale de la Place Bellecour'],
                ],
            ],
            [
                'name'               => 'Basilique de Fourvière',
                'description'        => 'Juchée sur la colline qui prie, la basilique Notre-Dame de Fourvière offre une vue panoramique exceptionnelle sur Lyon.',
                'lat'                => 45.7623,
                'lng'                => 4.8222,
                'validation_radius'  => 30,
                'order_index'        => 2,
                'estimated_time_min' => 15,
                'images' => [
                    ['url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3e/Lyon_-_Basilique_Notre-Dame_de_Fourvière_%28vue_de_nuit%29.jpg/1280px-Lyon_-_Basilique_Notre-Dame_de_Fourvière_%28vue_de_nuit%29.jpg', 'caption' => 'Basilique de nuit'],
                ],
            ],
            [
                'name'               => 'Traboules du Vieux Lyon',
                'description'        => 'Ces passages couverts traversant les immeubles de la Renaissance sont l\'âme secrète du Vieux Lyon.',
                'lat'                => 45.7631,
                'lng'                => 4.8269,
                'validation_radius'  => 40,
                'order_index'        => 3,
                'estimated_time_min' => 15,
                'images' => [
                    ['url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/Traboule_-_Lyon.jpg/800px-Traboule_-_Lyon.jpg', 'caption' => 'Entrée d\'une traboule'],
                ],
            ],
            [
                'name'               => 'Musée des Beaux-Arts',
                'description'        => 'Installé dans un ancien couvent bénédictin Place des Terreaux, l\'un des plus riches musées de France.',
                'lat'                => 45.7675,
                'lng'                => 4.8337,
                'validation_radius'  => 30,
                'order_index'        => 4,
                'estimated_time_min' => 20,
                'images' => [
                    ['url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/35/Lyon_Musee_des_Beaux_Arts_facade.jpg/1280px-Lyon_Musee_des_Beaux_Arts_facade.jpg', 'caption' => 'Façade du musée'],
                ],
            ],
            [
                'name'               => 'Institut Lumière',
                'description'        => 'Berceau du cinéma mondial, c\'est ici que les frères Lumière ont inventé le cinématographe en 1895.',
                'lat'                => 45.7479,
                'lng'                => 4.8544,
                'validation_radius'  => 30,
                'order_index'        => 5,
                'estimated_time_min' => 20,
                'images' => [
                    ['url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/Institut_Lumi%C3%A8re.jpg/800px-Institut_Lumi%C3%A8re.jpg', 'caption' => 'Villa Lumière'],
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
