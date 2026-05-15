<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE : achievements
 * Rôle  : Badges débloqués par un joueur au cours ou à la fin d'une partie.
 *         Affichés dans la page Score final et via BadgeToast.vue en temps réel.
 *
 *         Types de badges (colonne `type`) :
 *           - explorateur    : a découvert au moins 5 lieux dans une partie
 *           - rapide         : a résolu une énigme en moins de 60 secondes
 *           - sans_indice    : a terminé un parcours sans utiliser d'indice
 *           - maitre_ville   : a complété 100% des lieux d'une ville
 *           - speedrunner    : a terminé le parcours en moins de la moitié du temps imparti
 *
 * Liens :
 *   ← users.id         (user_id)
 *   ← game_sessions.id (game_session_id)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {

            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('game_session_id')
                  ->constrained('game_sessions')
                  ->cascadeOnDelete()
                  ->comment('Session durant laquelle le badge a été décroché');
            $table->enum('type', [
                'explorateur',
                'rapide',
                'sans_indice',
                'maitre_ville',
                'speedrunner',
            ])->comment('Type de badge — affiché avec son icône dans BadgeToast.vue');
            $table->timestamp('earned_at')->useCurrent()
                  ->comment("Moment exact de l'obtention du badge");

            $table->timestamp('created_at')->nullable();

            // ── Contrainte : un badge par type par session par joueur ──────
            $table->unique(['user_id', 'game_session_id', 'type'], 'uq_achievements_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
