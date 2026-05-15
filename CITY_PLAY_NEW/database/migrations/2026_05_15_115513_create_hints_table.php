<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE : hints
 * Rôle  : Indices progressifs d'une énigme, déblocables à la demande du joueur.
 *         Chaque indice a un coût en points (pénalité).
 *         Les indices sont ordonnés : l'indice 1 est le plus vague,
 *         l'indice 3 est presque la solution.
 *
 *         Logique métier dans HintController :
 *           1. Récupérer le prochain indice non encore utilisé (index le plus bas)
 *           2. Déduire points_penalty du score de la session
 *           3. Incrémenter scores.hints_used
 *
 * Liens :
 *   ← riddles.id (riddle_id)
 *   → scores.hints_used (comptabilisé indirectement)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hints', function (Blueprint $table) {

            $table->id();
            $table->foreignId('riddle_id')
                  ->constrained('riddles')
                  ->cascadeOnDelete()
                  ->comment('Énigme à laquelle appartient cet indice');
            $table->tinyInteger('index')
                  ->comment("Ordre de l'indice : 1 = vague ('C'est un bâtiment public'), 2 = précis ('Inauguré en 1887'), 3 = quasi-solution ('Cherchez la plaque à l'entrée')");
            $table->text('content')
                  ->comment("Texte de l'indice affiché dans HintPanel.vue");
            $table->integer('points_penalty')->default(10)
                  ->comment("Points déduits du score quand cet indice est demandé. Plus l'indice est précis, plus la pénalité est élevée.");

            $table->timestamp('created_at')->nullable();

            // ── Contrainte : un seul indice par index par énigme ──────────
            $table->unique(['riddle_id', 'index'], 'uq_hints_riddle_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hints');
    }
};
