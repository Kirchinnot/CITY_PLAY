<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE : cities
 * Rôle  : Représente une ville/aventure créée par un admin (mairie).
 *         C'est le conteneur principal du contenu de jeu.
 *         Une city contient plusieurs places (lieux), qui contiennent des riddles.
 *
 * Liens :
 *   ← users.id         (created_by)
 *   → places.city_id
 *   → invitations.city_id
 *   → game_sessions.city_id
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {

            // ── Identité ──────────────────────────────────────────────────
            $table->id();
            $table->string('name', 150)
                  ->comment('Nom affiché aux joueurs : "Aventure à Lyon", "Parcours historique Bordeaux"');
            $table->text('description')->nullable()
                  ->comment('Texte de présentation de la ville/aventure (500 car max recommandé)');
            $table->string('country', 100)->nullable();

            // ── Médias ───────────────────────────────────────────────────
            $table->string('banner_image', 255)->nullable()
                  ->comment('Chemin Storage — image de couverture de la ville');

            // ── Géolocalisation du centre ─────────────────────────────────
            $table->decimal('lat', 10, 7)->nullable()
                  ->comment('Latitude GPS du centre-ville — sert à centrer la carte Leaflet');
            $table->decimal('lng', 10, 7)->nullable()
                  ->comment('Longitude GPS du centre-ville');

            // ── Configuration de l'aventure ───────────────────────────────
            $table->integer('avg_duration_minutes')->default(90)
                  ->comment("Durée estimée d'un parcours complet — affichée aux joueurs avant de démarrer");
            $table->integer('retention_days')->default(365)
                  ->comment('Durée de conservation des données joueurs en jours (RGPD)');
            $table->boolean('is_published')->default(false)
                  ->comment('FALSE = brouillon admin, TRUE = visible aux joueurs via invitation');

            // ── Message de conclusion ─────────────────────────────────────
            $table->json('outro_config')->nullable()
                  ->comment('JSON : {text, restaurant_tip, shop_url, rating_url} — personnalisé par la mairie');

            // ── Propriétaire ──────────────────────────────────────────────
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->comment('Admin créateur de cette ville');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
