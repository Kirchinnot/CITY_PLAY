 <?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@cityplay.fr'],
            [
                'name'               => 'Admin CityPlay',
                'password'           => Hash::make('password'),
                'phone'              => '+33600000001',
                'role'               => 'admin',
                'two_factor_enabled' => false,
            ]
        );

        $players = [
            ['name' => 'Alice Martin', 'email' => 'alice@cityplay.fr', 'phone' => '+33611111111'],
            ['name' => 'Bob Dupont',   'email' => 'bob@cityplay.fr',   'phone' => '+33622222222'],
            ['name' => 'Carla Nguyen', 'email' => 'carla@cityplay.fr', 'phone' => '+33633333333'],
            ['name' => 'David Moreau', 'email' => 'david@cityplay.fr', 'phone' => '+33644444444'],
        ];

        foreach ($players as $p) {
            User::firstOrCreate(
                ['email' => $p['email']],
                [
                    'name'               => $p['name'],
                    'password'           => Hash::make('password'),
                    'phone'              => $p['phone'],
                    'role'               => 'player',
                    'two_factor_enabled' => false,
                ]
            );
        }
    }
}
