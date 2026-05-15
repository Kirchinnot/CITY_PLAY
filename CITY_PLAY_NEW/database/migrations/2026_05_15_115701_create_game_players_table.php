<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE : game_players
 * Rôle  : Table pivot entre une session et ses joueurs.
 *         Représente la "présence" d'un joueur dans une partie.
 *         Stocke l'état individuel de chaque joueur :
 *         position GPS actuelle, énigme en cours, statut de connexion.
 *
 *         Mise à jour en temps réel :
 *           - last_lat / last_lng / last_seen_at : toutes les 30s via
 *             POST /api/players/{id}/location (utilisé aussi pour anti-spoofing)
 *           - current_riddle_id : mis à jour quand un lieu est validé
 *
 *         Mode mercenaire : chaque joueur a son propre current_riddle_id
 *         Mode collectif  : tous partagent le même current_place_index de la session
 *
 * Liens :
 *   ← game_sessions.id (game_session_id)
 *   ← users.id         (user_id)
 *   ← riddles.id       (current_riddle_id — nullable)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_players', function (Blueprint $table) {

            $table->id();
            $table->foreignId('game_session_id')
                  ->constrained('game_sessions')
                  ->cascadeOnDelete()
                  ->comment('Session à laquelle ce joueur participe');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->comment('Compte du joueur');
            $table->timestamp('joined_at')->useCurrent()
                  ->comment('Moment où le joueur a rejoint le lobby');

            // ── État de jeu individuel ────────────────────────────────────
            $table->foreignId('current_riddle_id')
                  ->nullable()
                  ->constrained('riddles')
                  ->nullOnDelete()
                  ->comment('Énigme actuellement affichée à ce joueur. NULL = en déplacement vers un lieu.');

            // ── Position GPS temps réel ───────────────────────────────────
            $table->decimal('last_lat', 10, 7)->nullable()
                  ->comment('Dernière latitude connue — mise à jour toutes les 30s');
            $table->decimal('last_lng', 10, 7)->nullable()
                  ->comment('Dernière longitude connue');
            $table->timestamp('last_seen_at')->nullable()
                  ->comment('Timestamp de la dernière position reçue — sert à détecter les joueurs déconnectés (> 2min = inactif)');

            // ── Statut ────────────────────────────────────────────────────
            $table->boolean('is_active')->default(true)
                  ->comment('FALSE si le joueur a quitté la session volontairement');

            // ── Contrainte : un joueur ne peut rejoindre qu'une fois ──────
            $table->unique(['game_session_id', 'user_id'], 'uq_game_players_session_user');

            // ── Index ─────────────────────────────────────────────────────
            $table->index(['game_session_id', 'is_active'], 'idx_game_players_active');

            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_players');
    }
};
