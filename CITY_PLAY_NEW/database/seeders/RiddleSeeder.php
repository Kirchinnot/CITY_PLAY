<?php

namespace Database\Seeders;

use App\Models\Hint;
use App\Models\Place;
use App\Models\Riddle;
use Illuminate\Database\Seeder;

class RiddleSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Place Bellecour' => [
                'difficile' => [
                    'question' => 'En quelle année la statue équestre de Louis XIV a-t-elle été érigée sur cette place, et quel sculpteur en est l\'auteur ?',
                    'options'  => ['1825, Lemot', '1830, Chinard', '1815, Bosio', '1848, Pradier'],
                    'answer'   => '1825, Lemot',
                    'points_base' => 200
                ],
                'moyen'     => [
                    'question' => 'Quel surnom donne-t-on à cette place en raison de sa taille exceptionnelle ?',
                    'options'  => ['La plus grande place piétonne d\'Europe', 'La place du soleil', 'Le Forum des Gaules', 'Le Carré Royal'],
                    'answer'   => 'La plus grande place piétonne d\'Europe',
                    'points_base' => 100
                ],
                'facile'    => [
                    'question' => 'Quelle statue monumentale trône au centre de cette grande place de Lyon ?',
                    'options'  => ['Louis XIV', 'Napoléon Ier', 'Henri IV', 'Charles de Gaulle'],
                    'answer'   => 'Louis XIV',
                    'points_base' => 50
                ],
                'enfant'    => [
                    'question' => 'Sur cette immense place, tu vois un grand roi à cheval. Quel est son prénom ?',
                    'options'  => ['Louis', 'Jean', 'Paul', 'Arthur'],
                    'answer'   => 'Louis',
                    'points_base' => 25
                ],
            ],
            'Basilique de Fourvière' => [
                'difficile' => [
                    'question' => 'La basilique a été construite suite à un vœu formulé pendant quel événement historique de 1870–1871 ?',
                    'options'  => ['La guerre franco-prussienne', 'La Commune de Lyon', 'L\'inauguration du funiculaire', 'La crue du Rhône'],
                    'answer'   => 'La guerre franco-prussienne',
                    'points_base' => 200
                ],
                'moyen'     => [
                    'question' => 'Sur quelle colline la basilique est-elle construite et quel est son surnom populaire ?',
                    'options'  => ['Colline de Fourvière, la colline qui prie', 'Colline de la Croix-Rousse, la colline qui travaille', 'Colline de Saint-Just, la colline verte', 'Mont d\'Or, la colline dorée'],
                    'answer'   => 'Colline de Fourvière, la colline qui prie',
                    'points_base' => 100
                ],
                'facile'    => [
                    'question' => 'À qui est dédiée cette célèbre basilique dominant Lyon ?',
                    'options'  => ['Notre-Dame', 'Saint-Jean', 'Sainte-Blandine', 'Saint-Pothin'],
                    'answer'   => 'Notre-Dame',
                    'points_base' => 50
                ],
                'enfant'    => [
                    'question' => 'Cette grande église blanche est tout en haut d\'une colline. De quelle couleur est sa façade ?',
                    'options'  => ['Blanche', 'Rouge', 'Bleue', 'Verte'],
                    'answer'   => 'Blanche',
                    'points_base' => 25
                ],
            ],
            'Traboules du Vieux Lyon' => [
                'difficile' => [
                    'question' => 'Les traboules ont été utilisées stratégiquement durant quelle période de la Seconde Guerre mondiale à Lyon ?',
                    'options'  => ['La Résistance française', 'La collaboration', 'L\'exode', 'La libération de Paris'],
                    'answer'   => 'La Résistance française',
                    'points_base' => 200
                ],
                'moyen'     => [
                    'question' => 'De quel mot latin est issu le terme "traboule" et que signifie-t-il ?',
                    'options'  => ['Trans ambulare — traverser', 'Trabs — la poutre', 'Tabula — la table', 'Transire — passer'],
                    'answer'   => 'Trans ambulare — traverser',
                    'points_base' => 100
                ],
                'facile'    => [
                    'question' => 'Qu\'est-ce qu\'une traboule ?',
                    'options'  => ['Un passage couvert traversant un immeuble', 'Une spécialité culinaire lyonnaise', 'Un ancien puits romain', 'Une ruelle en impasse'],
                    'answer'   => 'Un passage couvert traversant un immeuble',
                    'points_base' => 50
                ],
                'enfant'    => [
                    'question' => 'Les traboules sont des passages secrets qui traversent les maisons. Vrai ou faux ?',
                    'options'  => ['Vrai', 'Faux'],
                    'answer'   => 'Vrai',
                    'points_base' => 25
                ],
            ],
            'Musée des Beaux-Arts' => [
                'difficile' => [
                    'question' => 'Quel ordre religieux occupait ce bâtiment avant sa transformation en musée, et en quelle année a-t-il ouvert ?',
                    'options'  => ['Les Bénédictines, 1803', 'Les Jésuites, 1792', 'Les Dominicains, 1810', 'Les Franciscains, 1801'],
                    'answer'   => 'Les Bénédictines, 1803',
                    'points_base' => 200
                ],
                'moyen'     => [
                    'question' => 'Dans quel type de bâtiment ancien ce musée a-t-il été installé ?',
                    'options'  => ['Un couvent bénédictin', 'Un ancien palais de justice', 'Une gare désaffectée', 'Un château royal'],
                    'answer'   => 'Un couvent bénédictin',
                    'points_base' => 100
                ],
                'facile'    => [
                    'question' => 'Sur quelle place de Lyon se trouve le Musée des Beaux-Arts ?',
                    'options'  => ['Place des Terreaux', 'Place Bellecour', 'Place des Jacobins', 'Place de la Comédie'],
                    'answer'   => 'Place des Terreaux',
                    'points_base' => 50
                ],
                'enfant'    => [
                    'question' => 'Ce musée est plein de tableaux et de sculptures. Comment s\'appelle la place devant lui ?',
                    'options'  => ['Place des Terreaux', 'Place Bellecour', 'Place de la Lune', 'Place du Soleil'],
                    'answer'   => 'Place des Terreaux',
                    'points_base' => 25
                ],
            ],
            'Institut Lumière' => [
                'difficile' => [
                    'question' => 'En quelle année et lors de quelle projection payante le cinématographe a-t-il été présenté pour la 1ère fois ?',
                    'options'  => ['28 décembre 1895, Grand Café', '14 juillet 1890, Casino de Paris', '20 mai 1895, Eden Théâtre', '1er janvier 1900, Exposition Universelle'],
                    'answer'   => '28 décembre 1895, Grand Café',
                    'points_base' => 200
                ],
                'moyen'     => [
                    'question' => 'Quel était le métier du père des frères Lumière, qui les a inspirés dans leur travail ?',
                    'options'  => ['Photographe', 'Boulanger', 'Ingénieur civil', 'Peintre en bâtiment'],
                    'answer'   => 'Photographe',
                    'points_base' => 100
                ],
                'facile'    => [
                    'question' => 'Quel appareil révolutionnaire les frères Lumière ont-ils inventé dans cette villa en 1895 ?',
                    'options'  => ['Le cinématographe', 'Le phonographe', 'Le télégraphe', 'Le microscope'],
                    'answer'   => 'Le cinématographe',
                    'points_base' => 50
                ],
                'enfant'    => [
                    'question' => 'Les frères Lumière ont inventé quelque chose qu\'on aime tous : regarder des films ! Comment ça s\'appelle ?',
                    'options'  => ['Le cinéma', 'La télévision', 'La radio', 'Internet'],
                    'answer'   => 'Le cinéma',
                    'points_base' => 25
                ],
            ],
        ];

        $hintsMap = [
            'difficile' => [
                ['index' => 1, 'content' => 'Observez les détails architecturaux autour de vous.', 'points_penalty' => 20],
                ['index' => 2, 'content' => 'Cherchez une plaque commémorative ou une inscription.', 'points_penalty' => 35],
                ['index' => 3, 'content' => 'La réponse est visible depuis l\'entrée principale.', 'points_penalty' => 50],
            ],
            'moyen' => [
                ['index' => 1, 'content' => 'Regardez autour de vous — la réponse est visible.', 'points_penalty' => 10],
                ['index' => 2, 'content' => 'Cherchez un panneau d\'information touristique.', 'points_penalty' => 20],
                ['index' => 3, 'content' => 'La réponse commence par la même lettre que le nom du lieu.', 'points_penalty' => 30],
            ],
            'facile' => [
                ['index' => 1, 'content' => 'Vous pouvez lire la réponse sur une plaque proche.', 'points_penalty' => 5],
                ['index' => 2, 'content' => 'C\'est un monument ou une personnalité célèbre.', 'points_penalty' => 10],
                ['index' => 3, 'content' => 'Demandez à quelqu\'un autour de vous !', 'points_penalty' => 15],
            ],
            'enfant' => [
                ['index' => 1, 'content' => 'Regarde bien la grande chose devant toi !', 'points_penalty' => 0],
                ['index' => 2, 'content' => 'Demande à un adulte de t\'aider.', 'points_penalty' => 0],
                ['index' => 3, 'content' => 'La réponse est sur le panneau en bas !', 'points_penalty' => 0],
            ],
        ];

        foreach ($data as $placeName => $riddles) {
            $place = Place::where('name', $placeName)->first();
            if (! $place) continue;

            foreach ($riddles as $difficulty => $info) {
                $riddle = Riddle::updateOrCreate(
                    ['place_id' => $place->id, 'difficulty' => $difficulty],
                    [
                        'title'              => ucfirst($difficulty) . ' — ' . $placeName,
                        'question'           => $info['question'],
                        'options'            => $info['options'],
                        'answer'             => $info['answer'],
                        'points_base'        => $info['points_base'],
                        'time_limit_seconds' => match($difficulty) {
                            'difficile' => 180,
                            'moyen'     => 300,
                            'facile'    => 420,
                            default     => 600,
                        },
                    ]
                );

                foreach ($hintsMap[$difficulty] as $hintData) {
                    Hint::updateOrCreate(
                        ['riddle_id' => $riddle->id, 'index' => $hintData['index']],
                        ['content' => $hintData['content'], 'points_penalty' => $hintData['points_penalty']]
                    );
                }
            }
        }
    }
}
