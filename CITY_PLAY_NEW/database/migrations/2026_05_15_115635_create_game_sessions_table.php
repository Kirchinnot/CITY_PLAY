<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE : game_sessions
 * Rôle  : Une partie active ou terminée.
 *         Créée quand le joueur principal clique "Démarrer" depuis la page Lobby.
 *         Contient l'état courant de la partie : statut, lieu actif, temps de pause.
 *
 *         Cycle de vie des statuts :
 *           pending → active → paused → active → completed
 *                                              ↘ abandoned
 *
 *         Logique GameSessionService :
 *           - start()   : pending → active, sélectionne les lieux selon durée+locomotion
 *           - pause()   : active → paused, enregistre paused_at
 *           - resume()  : paused → active, cumule total_pause_seconds
 *           - complete(): → completed, enregistre completed_at
 *           - abandon() : → abandoned
 *
 * Liens :
 *   ← invitations.id  (invitation_id)
 *   ← cities.id       (city_id)
 *   ← users.id        (host_user_id)
 *   → game_players.game_session_id
 *   → session_places.game_session_id
 *   → scores.game_session_id
 *   → achievements.game_session_id
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_sessions', function (Blueprint $table) {

            // ── Identité ──────────────────────────────────────────────────
            $table->id();
            $table->foreignId('invitation_id')
                  ->constrained('invitations')
                  ->comment("Invitation d'origine — porte les paramètres mode/difficulté/locomotion");
            $table->foreignId('city_id')
                  ->constrained('cities')
                  ->comment('Dénormalisé depuis invitation pour accès rapide sans join');
            $table->foreignId('host_user_id')
                  ->constrained('users')
                  ->comment('Joueur principal — seul à pouvoir faire pause/abandon global');

            // ── Paramètres de jeu (copiés depuis invitation à la création) ─
            $table->enum('mode', ['solo','collectif', 'mercenaire'])->default('collectif');
            $table->enum('difficulty', ['enfant', 'facile', 'moyen', 'difficile'])->default('moyen');
            $table->enum('locomotion', ['marche', 'velo', 'moto', 'voiture'])->default('marche');
            $table->integer('available_minutes')
                  ->comment('Durée totale disponible — sert au calcul du bonus temps final');

            // ── État de la partie ─────────────────────────────────────────
            $table->enum('status', ['pending', 'active', 'paused', 'completed', 'abandoned'])
                  ->default('pending')
                  ->comment('pending = lobby en attente, active = jeu en cours, paused = pause joueur, completed = fini normalement, abandoned = abandonné');
            $table->integer('current_place_index')->default(0)
                  ->comment('Index du lieu actif dans session_places (order_index) — incrémenté après chaque lieu validé');

            // ── Progression ───────────────────────────────────────────────
            $table->integer('total_places')->default(0)
                  ->comment('Nombre total de lieux sélectionnés pour cette session — calculé par GameSessionService au démarrage');
            $table->integer('solved_places')->default(0)
                  ->comment('Nombre de lieux découverts — incrément à chaque lieu validé');

            // ── Timing ───────────────────────────────────────────────────
            $table->timestamp('started_at')->nullable()
                  ->comment('Timestamp du clic "Démarrer" — base de calcul du temps écoulé');
            $table->timestamp('paused_at')->nullable()
                  ->comment('Timestamp de la dernière mise en pause (NULL si en cours)');
            $table->integer('total_pause_seconds')->default(0)
                  ->comment('Cumul des durées de pause — soustrait au temps total pour le scoring');
            $table->timestamp('completed_at')->nullable()
                  ->comment('Timestamp de fin (NULL si pas encore terminé)');

            $table->timestamps();

            // ── Index ─────────────────────────────────────────────────────
            $table->index(['host_user_id', 'status'], 'idx_sessions_user_status');
            $table->index('status', 'idx_sessions_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};
