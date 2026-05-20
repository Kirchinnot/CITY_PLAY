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
            'Marché Dantokpa' => [
                'difficile' => [
                    'question' => 'Quel produit traditionnel est vendu sous forme de pâte parfumée dans ce marché ?','options'  => ['Gombo frais', 'Yam rôti', 'Akassa', 'Choukouya'],
                    'answer'   => 'Akassa',
                    'points_base' => 200
                ],
                'moyen'     => [
                    'question' => 'Quel nom porte le grand marché de Cotonou connu pour ses tissus, épices et marchandises ?',
                    'options'  => ['Dantokpa', 'Zongo', 'Ganhi', 'Agla'],
                    'answer'   => 'Dantokpa',
                    'points_base' => 100
                ],
                'facile'    => [
                    'question' => 'Dans quelle ville se trouve le marché Dantokpa ?',
                    'options'  => ['Cotonou', 'Porto-Novo', 'Abomey', 'Ouidah'],
                    'answer'   => 'Cotonou',
                    'points_base' => 50
                ],
                'enfant'    => [
                    'question' => 'Ce marché est très grand et bruyant. Dans quelle ville du Bénin est-il ?',
                    'options'  => ['Cotonou', 'Lyon', 'Paris', 'Abidjan'],
                    'answer'   => 'Cotonou',
                    'points_base' => 25
                ],
            ],
            'Plage des Cocotiers' => [
                'difficile' => [
                    'question' => 'Quel nom donnent les habitants de Cotonou à la plage où l\'on trouve des palmiers et des pêcheurs au bord de la mer ?',
                    'options'  => ['Plage des Cocotiers', 'Plage de Fidjrossè', 'Plage de Grand-Popo', 'Plage du Rocher'],
                    'answer'   => 'Plage des Cocotiers',
                    'points_base' => 200
                ],
                'moyen'     => [
                    'question' => 'Quel élément naturel borde la plage des Cocotiers ?',
                    'options'  => ['Le Golfe de Guinée', 'Le fleuve Niger', 'Le lac Nokoué', 'La rivière Sô'],
                    'answer'   => 'Le Golfe de Guinée',
                    'points_base' => 100
                ],
                'facile'    => [
                    'question' => 'Sur quelle étendue d\'eau se trouve la plage des Cocotiers ?',
                    'options'  => ['La mer', 'Un lac', 'Une rivière', 'Une piscine'],
                    'answer'   => 'La mer',
                    'points_base' => 50
                ],
                'enfant'    => [
                    'question' => 'Il y a des cocotiers et du sable. Est-ce que c\'est une plage ?',
                    'options'  => ['Oui', 'Non'],
                    'answer'   => 'Oui',
                    'points_base' => 25
                ],
            ],
            'Musée Historique de Ouidah' => [
                'difficile' => [
                    'question' => 'Quel royaume ancien est mis à l\'honneur dans ce musée historique ?',
                    'options'  => ['Dahomey', 'Zululand', 'Mali', 'Songhaï'],
                    'answer'   => 'Dahomey',
                    'points_base' => 200
                ],
                'moyen'     => [
                    'question' => 'Quel thème principal trouve-t-on dans ce musée d\'Ouidah ?',
                    'options'  => ['Histoire et mémoire de l\'Afrique de l\'Ouest', 'Art contemporain européen', 'Faune marine', 'Aviation'],
                    'answer'   => 'Histoire et mémoire de l\'Afrique de l\'Ouest',
                    'points_base' => 100
                ],
                'facile'    => [
                    'question' => 'Dans quelle ville se trouve le Musée Historique de Ouidah ?',
                    'options'  => ['Ouidah', 'Cotonou', 'Porto-Novo', 'Abomey'],
                    'answer'   => 'Ouidah',
                    'points_base' => 50
                ],
                'enfant'    => [
                    'question' => 'Ce musée raconte des histoires anciennes. Est-il à Ouidah ?',
                    'options'  => ['Oui', 'Non'],
                    'answer'   => 'Oui',
                    'points_base' => 25
                ],
            ],
            'Porte du Non-Retour' => [
                'difficile' => [
                    'question' => 'La Porte du Non-Retour rappelle quel épisode historique douloureux ?',
                    'options'  => ['La traite transatlantique des esclaves', 'La révolution de 1910', 'La guerre coloniale', 'L\'indépendance du Bénin'],
                    'answer'   => 'La traite transatlantique des esclaves',
                    'points_base' => 200
                ],
                'moyen'     => [
                    'question' => 'Quel symbole est associé à la Porte du Non-Retour ?',
                    'options'  => ['Mémoire et résilience', 'Richesse et fortune', 'Joie et fête', 'Commerce'],
                    'answer'   => 'Mémoire et résilience',
                    'points_base' => 100
                ],
                'facile'    => [
                    'question' => 'La Porte du Non-Retour se trouve près de quel lieu ?',
                    'options'  => ['La mer', 'Une montagne', 'Une forêt', 'Un désert'],
                    'answer'   => 'La mer',
                    'points_base' => 50
                ],
                'enfant'    => [
                    'question' => 'Ce monument est près de l\'océan. Est-ce que c\'est un lieu triste ou joyeux ?',
                    'options'  => ['Triste', 'Joyeux'],
                    'answer'   => 'Triste',
                    'points_base' => 25
                ],
            ],
            'Palais Royal d\'Abomey' => [
                'difficile' => [
                    'question' => 'Le Palais Royal d\'Abomey était le centre de quel royaume africain ?',
                    'options'  => ['Dahomey', 'Ashanti', 'Kanem', 'Watts'],
                    'answer'   => 'Dahomey',
                    'points_base' => 200
                ],
                'moyen'     => [
                    'question' => 'Quel élément orne souvent les murs du palais royal à Abomey ?',
                    'options'  => ['Bas-reliefs historiques', 'Graffitis modernes', 'Peintures flamandes', 'Sculptures de glace'],
                    'answer'   => 'Bas-reliefs historiques',
                    'points_base' => 100
                ],
                'facile'    => [
                    'question' => 'Le Palais Royal d\'Abomey était habité par des rois. Comment s\'appelle ce type de lieu ?',
                    'options'  => ['Palais', 'École', 'Marché', 'Pont'],
                    'answer'   => 'Palais',
                    'points_base' => 50
                ],
                'enfant'    => [
                    'question' => 'C\'est un grand bâtiment où vivait un roi. Est-ce un palais ?',
                    'options'  => ['Oui', 'Non'],
                    'answer'   => 'Oui',
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
