<?php

namespace Database\Seeders;

use App\Models\Place;
use App\Models\Riddle;
use App\Models\RiddleAnswer;
use App\Models\RiddleImage;
use Illuminate\Database\Seeder;

class RiddleSeeder extends Seeder
{
    public function run(): void
    {
        // Structure : place_name => [ difficulty => [question, answer, points, time_limit] ]
        $data = [
            'Place Bellecour' => [
                'force_3' => [
                    'question'    => 'En quelle année la statue équestre de Louis XIV a-t-elle été érigée sur cette place, et quel sculpteur en est l\'auteur ?',
                    'answer'      => '1825, Lemot',
                    'points'      => 30,
                    'time_limit'  => 90,
                ],
                'force_2' => [
                    'question'    => 'Quel surnom donne-t-on à cette place en raison de sa taille exceptionnelle ?',
                    'answer'      => 'La plus grande place piétonne d\'Europe',
                    'points'      => 20,
                    'time_limit'  => 120,
                ],
                'force_1' => [
                    'question'    => 'Quelle statue monumentale trône au centre de cette grande place de Lyon ?',
                    'answer'      => 'Louis XIV',
                    'points'      => 10,
                    'time_limit'  => 150,
                ],
                'child' => [
                    'question'    => 'Sur cette immense place, tu vois un grand roi à cheval. Quel est son prénom ?',
                    'answer'      => 'Louis',
                    'points'      => 5,
                    'time_limit'  => 180,
                ],
            ],
            'Basilique de Fourvière' => [
                'force_3' => [
                    'question'    => 'La basilique a été construite suite à un vœu formulé pendant quel événement historique de 1870–1871 ?',
                    'answer'      => 'La guerre franco-prussienne',
                    'points'      => 30,
                    'time_limit'  => 90,
                ],
                'force_2' => [
                    'question'    => 'Sur quelle colline la basilique est-elle construite et quel est son surnom populaire ?',
                    'answer'      => 'Colline de Fourvière, la colline qui prie',
                    'points'      => 20,
                    'time_limit'  => 120,
                ],
                'force_1' => [
                    'question'    => 'À qui est dédiée cette célèbre basilique dominant Lyon ?',
                    'answer'      => 'Notre-Dame',
                    'points'      => 10,
                    'time_limit'  => 150,
                ],
                'child' => [
                    'question'    => 'Cette grande église blanche est tout en haut d\'une colline. De quelle couleur est sa façade ?',
                    'answer'      => 'Blanche',
                    'points'      => 5,
                    'time_limit'  => 180,
                ],
            ],
            'Traboules du Vieux Lyon' => [
                'force_3' => [
                    'question'    => 'Les traboules ont été utilisées de manière stratégique durant quelle période de la Seconde Guerre mondiale à Lyon ?',
                    'answer'      => 'La Résistance française',
                    'points'      => 30,
                    'time_limit'  => 90,
                ],
                'force_2' => [
                    'question'    => 'De quel mot latin est issu le terme "traboule" et que signifie-t-il ?',
                    'answer'      => 'Trans ambulare, traverser',
                    'points'      => 20,
                    'time_limit'  => 120,
                ],
                'force_1' => [
                    'question'    => 'Qu\'est-ce qu\'une traboule ?',
                    'answer'      => 'Un passage couvert traversant un immeuble',
                    'points'      => 10,
                    'time_limit'  => 150,
                ],
                'child' => [
                    'question'    => 'Les traboules sont des passages secrets qui traversent les maisons. Vrai ou faux ?',
                    'answer'      => 'Vrai',
                    'points'      => 5,
                    'time_limit'  => 180,
                ],
            ],
            'Musée des Beaux-Arts de Lyon' => [
                'force_3' => [
                    'question'    => 'Quel ordre religieux occupait ce bâtiment avant sa transformation en musée, et en quelle année le musée a-t-il ouvert ?',
                    'answer'      => 'Les Bénédictines, 1803',
                    'points'      => 30,
                    'time_limit'  => 90,
                ],
                'force_2' => [
                    'question'    => 'Dans quel type de bâtiment ancien ce musée a-t-il été installé ?',
                    'answer'      => 'Un couvent bénédictin',
                    'points'      => 20,
                    'time_limit'  => 120,
                ],
                'force_1' => [
                    'question'    => 'Sur quelle place de Lyon se trouve le Musée des Beaux-Arts ?',
                    'answer'      => 'Place des Terreaux',
                    'points'      => 10,
                    'time_limit'  => 150,
                ],
                'child' => [
                    'question'    => 'Ce musée est plein de tableaux et de sculptures. Comment s\'appelle la place devant lui ?',
                    'answer'      => 'Place des Terreaux',
                    'points'      => 5,
                    'time_limit'  => 180,
                ],
            ],
            'Institut Lumière' => [
                'force_3' => [
                    'question'    => 'En quelle année précise et lors de quelle projection publique payante le cinématographe a-t-il été présenté pour la première fois à Paris ?',
                    'answer'      => '28 décembre 1895, Grand Café boulevard des Capucines',
                    'points'      => 30,
                    'time_limit'  => 90,
                ],
                'force_2' => [
                    'question'    => 'Quel était le métier du père des frères Lumière, qui les a inspirés dans leur travail sur la photographie ?',
                    'answer'      => 'Photographe et fabricant de plaques photographiques',
                    'points'      => 20,
                    'time_limit'  => 120,
                ],
                'force_1' => [
                    'question'    => 'Quel appareil révolutionnaire les frères Lumière ont-ils inventé dans cette villa en 1895 ?',
                    'answer'      => 'Le cinématographe',
                    'points'      => 10,
                    'time_limit'  => 150,
                ],
                'child' => [
                    'question'    => 'Les frères Lumière ont inventé quelque chose qu\'on aime tous : regarder des films ! Comment ça s\'appelle ?',
                    'answer'      => 'Le cinéma',
                    'points'      => 5,
                    'time_limit'  => 180,
                ],
            ],
        ];

        foreach ($data as $placeName => $riddles) {
            $place = Place::where('name', $placeName)->first();
            if (! $place) {
                continue;
            }

            foreach ($riddles as $difficulty => $info) {
                $riddle = Riddle::firstOrCreate(
                    ['place_id' => $place->id, 'difficulty' => $difficulty],
                    [
                        'title'              => ucfirst(str_replace('_', ' ', $difficulty)) . ' – ' . $placeName,
                        'question'           => $info['question'],
                        'points'             => $info['points'],
                        'time_limit_seconds' => $info['time_limit'],
                    ]
                );

                RiddleAnswer::firstOrCreate(
                    ['riddle_id' => $riddle->id],
                    ['answer' => $info['answer'], 'created_at' => now()]
                );
            }
        }
    }
}
