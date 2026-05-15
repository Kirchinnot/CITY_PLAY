<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('riddles', function (Blueprint $table) {

            // ── Identité ──────────────────────────────────────────────────
            $table->id();
            $table->foreignId('place_id')
                  ->constrained('places')
                  ->cascadeOnDelete()
                  ->comment('Lieu auquel cette énigme est rattachée');
            $table->string('title', 150)->nullable()
                  ->comment("Titre court de l'énigme — affiché dans l'en-tête RiddleCard.vue");
            $table->text('question')
                  ->comment("Texte complet de l'énigme affiché au joueur");

            // ── Difficulté ────────────────────────────────────────────────
            $table->enum('difficulty', ['enfant', 'facile', 'moyen', 'difficile'])
                  ->comment('Niveau de difficulté : enfant (iconique), facile=force1, moyen=force2, difficile=force3');
            $table->integer('points_base')->default(100)
                  ->comment('Points de base accordés pour cette énigme (avant modificateurs rapidité/indices)');
            $table->integer('time_limit_seconds')->default(300)
                  ->comment('Temps max en secondes — base de calcul du bonus rapidité');

            // ── Réponse attendue (QCM) ────────────────────────────────────
            $table->json('options')
                  ->comment('Tableau JSON des choix possibles (ex: ["Option A", "Option B", ...])');
            $table->string('answer', 255)
                  ->comment('La réponse correcte (doit correspondre à l\'une des options)');

            $table->timestamps();

            // ── Index ─────────────────────────────────────────────────────
            $table->index(['place_id', 'difficulty'], 'idx_riddles_place_difficulty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riddles');
    }
};
