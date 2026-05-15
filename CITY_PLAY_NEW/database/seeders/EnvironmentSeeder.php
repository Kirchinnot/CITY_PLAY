<?php

namespace Database\Seeders;

use App\Models\Environment;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnvironmentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        Environment::firstOrCreate(
            ['name' => 'Découverte du Vieux Lyon'],
            [
                'city'           => 'Lyon',
                'country'        => 'France',
                'description'    => 'Partez à la découverte des trésors cachés du Vieux Lyon, de la Place Bellecour aux traboules de la Renaissance.',
                'cover_image'    => null,
                'retention_days' => 365,
                'is_published'   => true,
                'conclusion'     => 'Bravo pour votre parcours ! Prolongez l\'aventure : déjeuner au Bouchon Lyonnais (2 rue Mercière), boutique souvenirs Chez Madeleine (Place des Terreaux). Notez votre expérience sur notre site !',
                'created_by'     => $admin->id,
            ]
        );

        Environment::firstOrCreate(
            ['name' => 'Secrets de Montmartre'],
            [
                'city'           => 'Paris',
                'country'        => 'France',
                'description'    => 'Explorez les ruelles artistiques et les anecdotes historiques de la Butte Montmartre.',
                'cover_image'    => null,
                'retention_days' => 180,
                'is_published'   => false,
                'conclusion'     => 'Félicitations ! Terminez votre visite au café des Deux Moulins et emportez un souvenir de la Place du Tertre.',
                'created_by'     => $admin->id,
            ]
        );
    }
}
