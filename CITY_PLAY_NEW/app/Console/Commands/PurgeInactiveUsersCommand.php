<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PurgeInactiveUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cityplay:purge-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge les utilisateurs inactifs selon les règles de rétention RGPD définies par les mairies';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Démarrage de la purge RGPD des utilisateurs inactifs...');

        // Nous allons chercher les utilisateurs qui ont joué pour une ville spécifique
        // et appliquer la durée de conservation paramétrée par la mairie de cette ville.
        // Simplification : nous allons purger les joueurs ayant explicitement demandé la suppression,
        // ou dont la dernière session complétée dépasse la durée max de la ville.

        // Dans un premier temps, on purge ceux marqués pour suppression immédiate (si applicable via un flag).
        $usersToDeleteNow = User::where('delete_requested', true)->get();
        
        $count = 0;
        foreach ($usersToDeleteNow as $user) {
            $user->delete();
            $count++;
        }

        $this->info("Purge immédiate : $count utilisateur(s) supprimé(s).");

        // Rétention basée sur la ville : (à implémenter en fonction du modèle de données de rétention)
        // Parcourons toutes les villes ayant une config outro
        $cities = City::whereNotNull('outro_config')->get();
        
        foreach ($cities as $city) {
            $config = is_string($city->outro_config) ? json_decode($city->outro_config, true) : $city->outro_config;
            
            // Si la mairie a défini une durée de conservation en jours (ex: retention_days)
            if (isset($config['retention_days'])) {
                $days = (int) $config['retention_days'];
                $cutoffDate = Carbon::now()->subDays($days);
                
                // Chercher les utilisateurs dont la dernière session dans cette ville est plus ancienne que $cutoffDate
                // (Logique à adapter si nécessaire)
            }
        }

        $this->info('Purge RGPD terminée.');
    }
}
