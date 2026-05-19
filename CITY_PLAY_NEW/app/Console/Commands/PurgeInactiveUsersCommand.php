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

        // 1. Purge immédiate des utilisateurs ayant explicitement demandé la suppression
        $usersToDeleteNow = User::where('delete_requested', true)->get();
        $countImmediate = 0;
        foreach ($usersToDeleteNow as $user) {
            $user->delete();
            $countImmediate++;
        }
        $this->info("Purge immédiate : $countImmediate utilisateur(s) supprimé(s).");

        // 2. Rétention basée sur les paramètres des villes
        // On cherche les utilisateurs (joueurs) dont la dernière activité dépasse la limite de rétention
        // de la dernière ville visitée (ou la limite par défaut).
        $cities = City::all();
        $countRetention = 0;

        foreach ($cities as $city) {
            $days = $city->retention_days ?? 365; // Défaut 1 an
            $cutoffDate = Carbon::now()->subDays($days);

            // Trouver les utilisateurs dont la dernière session dans cette ville est trop ancienne
            // et qui n'ont pas d'autres sessions plus récentes ailleurs.
            $expiredUsers = User::where('role', 'player')
                ->whereHas('gameSessions', function($query) use ($city, $cutoffDate) {
                    $query->where('city_id', $city->id)
                          ->where('updated_at', '<', $cutoffDate);
                })
                ->whereDoesntHave('gameSessions', function($query) use ($cutoffDate) {
                    $query->where('updated_at', '>=', $cutoffDate);
                })
                ->get();

            foreach ($expiredUsers as $user) {
                $user->delete();
                $countRetention++;
            }
        }

        $this->info("Purge par rétention : $countRetention utilisateur(s) supprimé(s).");
        $this->info('Purge RGPD terminée.');
    }
}
