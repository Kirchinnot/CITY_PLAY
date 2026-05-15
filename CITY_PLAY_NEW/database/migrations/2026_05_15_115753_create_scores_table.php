<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE : scores
 * Rôle  : Enregistre le résultat de chaque tentative de résolution d'énigme.
 *         C'est ici que ScoreCalculator insère après chaque validation.
 *
 *         Mode collectif  : user_id = NULL (score d'équipe, rattaché à la session)
 *         Mode mercenaire : user_id = id du joueur résolvant
 *
 *         Calcul des points (ScoreCalculator) :
 *           - Rapidité    : 100 × (1 − time_taken / riddle.time_limit_seconds)
 *           - Distance    : 50 pts fixe si GPS valide
 *           - Indices     : 30 pts × indices non demandés
 *           - Difficulté  : points_base du riddle (50/100/200 selon level)
 *           Total = sum des critères ci-dessus, min 0.
 *
 *         is_blocked : mode mercenaire uniquement — TRUE si une autre équipe
 *         a résolu cette énigme en premier, bloquant les autres.
 *
 * Liens :
 *   ← game_sessions.id (game_session_id)
 *   ← users.id         (user_id — NULL en mode collectif)
 *   ← riddles.id       (riddle_id)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scores', function (Blueprint $table) {

            $table->id();
            $table->foreignId('game_session_id')
                  ->constrained('game_sessions')
                  ->cascadeOnDelete()
                  ->comment('Session dans laquelle cette résolution a eu lieu');
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment("NULL en mode collectif (score d'équipe). ID du joueur en mode mercenaire.");
            $table->foreignId('riddle_id')
                  ->constrained('riddles')
                  ->comment('Énigme résolue (ou tentée)');

            // ── Points détaillés ──────────────────────────────────────────
            $table->integer('points_earned')->default(0)
                  ->comment('Total des points calculés par ScoreCalculator pour cette résolution');
            $table->integer('points_speed')->default(0)
                  ->comment('Composante rapidité : 100 × (1 − t/tmax)');
            $table->integer('points_distance')->default(0)
                  ->comment('Composante déplacement GPS : 50 pts si GPS valide');
            $table->integer('points_hints_bonus')->default(0)
                  ->comment('Composante indices non utilisés : 30 pts × (dispo − utilisés)');
            $table->integer('points_difficulty')->default(0)
                  ->comment("Points de base selon le level de l'énigme");

            // ── Métriques de résolution ───────────────────────────────────
            $table->integer('hints_used')->default(0)
                  ->comment("Nombre d'indices demandés pour cette énigme");
            $table->integer('time_taken_seconds')->nullable()
                  ->comment("Durée de résolution en secondes depuis l'affichage de l'énigme");
            $table->decimal('distance_m', 8, 2)->nullable()
                  ->comment('Distance en mètres entre la position du joueur et le lieu cible au moment de la validation GPS');

            // ── Mode mercenaire ───────────────────────────────────────────
            $table->boolean('is_blocked')->default(false)
                  ->comment('TRUE = énigme bloquée par un autre joueur (mode mercenaire) — ce joueur ne peut plus soumettre de réponse');

            $table->timestamp('resolved_at')->nullable()
                  ->comment('Timestamp de la validation — base du calcul de rapidité');

            $table->timestamp('created_at')->nullable();

            // ── Index ─────────────────────────────────────────────────────
            $table->index(['game_session_id', 'user_id'], 'idx_scores_session_user');
            $table->index(['game_session_id', 'riddle_id'], 'idx_scores_session_riddle');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
