<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE : place_images
 * Rôle  : Photos d'un lieu (3 à 4 max recommandées).
 *         Affichées APRÈS que le joueur a résolu l'énigme du lieu,
 *         comme "récompense de découverte".
 *         Également affichées dans le récapitulatif fin de partie
 *         pour les lieux non découverts.
 *
 * Liens :
 *   ← places.id (place_id)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('place_images', function (Blueprint $table) {

            $table->id();
            $table->foreignId('place_id')
                  ->constrained('places')
                  ->cascadeOnDelete()
                  ->comment('Lieu auquel appartient cette image');
            $table->string('image_url', 255)
                  ->comment('Chemin Storage ou URL Cloudinary — redimensionnée par intervention/image');
            $table->integer('display_order')->default(1)
                  ->comment("Ordre d'affichage : 1 = image principale (thumbnail), 2-4 = galerie");
            $table->string('caption', 255)->nullable()
                  ->comment('Légende optionnelle affichée sous la photo');

            $table->timestamp('created_at')->nullable();

            // ── Index ─────────────────────────────────────────────────────
            $table->index(['place_id', 'display_order'], 'idx_place_images_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_images');
    }
};
