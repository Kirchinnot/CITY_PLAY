<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE : places
 * Rôle  : Lieu physique géolocalisé dans une ville.
 *         Le joueur doit se déplacer jusqu'à ce lieu pour valider l'énigme associée.
 *         Chaque place a un rayon GPS de validation et un ordre dans le parcours.
 *
 * Liens :
 *   ← cities.id        (city_id)
 *   → place_images.place_id
 *   → riddles.place_id
 *   → session_places.place_id
 *   → game_sessions.current_place_index (indirectement via order_index)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table) {

            // ── Identité ──────────────────────────────────────────────────
            $table->id();
            $table->foreignId('city_id')
                  ->constrained('cities')
                  ->cascadeOnDelete()
                  ->comment('Ville à laquelle ce lieu appartient');
            $table->string('name', 150)
                  ->comment('Nom du lieu : "Fontaine des Jacobins", "Vieux Port"');
            $table->text('description')->nullable()
                  ->comment("Texte affiché après résolution de l'énigme (découverte du lieu)");

            // ── Géolocalisation exacte ────────────────────────────────────
            $table->decimal('lat', 10, 7)
                  ->comment('Latitude GPS exacte du point à atteindre');
            $table->decimal('lng', 10, 7)
                  ->comment('Longitude GPS exacte du point à atteindre');
            $table->integer('validation_radius')->default(25)
                  ->comment('Rayon en mètres : le joueur doit être dans ce cercle pour valider. Conseils : marche=15-30m, vélo=30-60m, voiture=50-100m');

            // ── Ordre & timing ────────────────────────────────────────────
            $table->integer('order_index')->default(1)
                  ->comment("Ordre d'affichage dans le parcours — défini par l'admin (popularité, logique géo, etc.)");
            $table->integer('estimated_time_min')->default(10)
                  ->comment("Temps estimé pour atteindre ce lieu depuis le précédent — sert au calcul du nombre d'énigmes selon la durée choisie");

            $table->timestamps();

            // ── Index ─────────────────────────────────────────────────────
            $table->index(['city_id', 'order_index'], 'idx_places_city_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
