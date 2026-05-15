<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE : session_places
 * Rôle  : Liste des lieux sélectionnés pour une session donnée.
 *         Générée une seule fois par GameSessionService au démarrage,
 *         en fonction de la durée disponible, de la locomotion et de l'order_index.
 *
 *         Exemple : une ville a 12 lieux, mais avec 60min à pied,
 *         GameSessionService n'en sélectionne que 4. Ces 4 lieux sont
 *         insérés ici avec leur order_index propre à la session.
 *
 *         Progression :
 *           - is_completed passe à TRUE quand le joueur valide le GPS + réponse du lieu
 *           - completed_at permet de calculer le temps par lieu
 *
 * Liens :
 *   ← game_sessions.id (game_session_id)
 *   ← places.id        (place_id)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_places', function (Blueprint $table) {

            $table->id();
            $table->foreignId('game_session_id')
                  ->constrained('game_sessions')
                  ->cascadeOnDelete()
                  ->comment('Session à laquelle appartient cette entrée');
            $table->foreignId('place_id')
                  ->constrained('places')
                  ->comment('Lieu physique concerné');

            // ── Ordre dans cette session spécifique ───────────────────────
            $table->integer('order_index')
                  ->comment('Position de ce lieu dans le parcours de CETTE session (peut différer de place.order_index si tous les lieux ne sont pas inclus)');

            // ── Progression ───────────────────────────────────────────────
            $table->boolean('is_completed')->default(false)
                  ->comment('TRUE = le joueur a validé GPS + réponse pour ce lieu');
            $table->timestamp('completed_at')->nullable()
                  ->comment('Timestamp de validation — sert à calculer le temps passé sur ce lieu');

            $table->timestamp('created_at')->nullable();

            // ── Contrainte : un lieu ne peut apparaître qu'une fois par session ─
            $table->unique(['game_session_id', 'place_id'], 'uq_session_places_uniq');

            // ── Index ─────────────────────────────────────────────────────
            $table->index(['game_session_id', 'order_index'], 'idx_session_places_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_places');
    }
};
