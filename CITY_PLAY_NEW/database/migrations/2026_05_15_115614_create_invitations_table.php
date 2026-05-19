<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE : invitations
 * Rôle  : Lien d'invitation généré par l'admin pour démarrer une aventure.
 *         Contient tous les paramètres de jeu pré-configurés par la mairie :
 *         mode, difficulté, locomotion, max joueurs, expiration.
 *
 *         Le token est partagé via mail/SMS/WhatsApp/QR code.
 *         Route joueur : GET /join/{token}
 *
 *         Flux :
 *           Admin crée invitation → token généré → lien envoyé → joueur arrive
 *           sur /join/{token} → crée son compte → démarre la session avec
 *           les paramètres de l'invitation.
 *
 * Liens :
 *   ← users.id   (created_by — admin)
 *   ← cities.id  (city_id)
 *   → game_sessions.invitation_id
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {

            // ── Identité ──────────────────────────────────────────────────
            $table->id();
            $table->string('token', 64)->unique()
                  ->comment("UUID unique — ex: Str::uuid(). Constitue l'URL : /join/550e8400-e29b...");
            $table->foreignId('city_id')
                  ->constrained('cities')
                  ->cascadeOnDelete()
                  ->comment('Ville concernée par cette invitation');
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->comment('Admin ayant généré cette invitation');

            // ── Paramètres de session pré-configurés ──────────────────────
            $table->enum('mode', ['solo','collectif', 'mercenaire'])->default('collectif')
                  ->comment("collectif = score d'équipe partagé, mercenaire = compétition individuelle");
            $table->enum('difficulty', ['enfant', 'facile', 'moyen', 'difficile'])->default('moyen')
                  ->comment('Niveau des énigmes sélectionnées pour cette session');
            $table->enum('locomotion', ['marche', 'velo', 'moto', 'voiture'])->default('marche')
                  ->comment('Moyen de transport — influence le nombre de lieux atteignables selon la durée');
            $table->integer('max_players')
                  ->default(10)
                  ->comment('Nombre max de joueurs par équipe. Strictement limité à 10 pour le MVP.');

            // ── Durée & validité ──────────────────────────────────────────
            $table->integer('duration_minutes')->default(90)
                  ->comment('Durée de jeu prévue — transmise à GameSessionService pour la sélection des lieux');
            $table->timestamp('expires_at')->nullable()
                  ->comment("Date d'expiration du lien. NULL = permanent. Vérifié à chaque accès /join/{token}");
            $table->integer('used_count')->default(0)
                  ->comment("Compteur d'utilisations — incrément à chaque création de session depuis ce lien");

            $table->timestamps();

            // ── Index ─────────────────────────────────────────────────────
            $table->index('token', 'idx_invitations_token');
            $table->index('expires_at', 'idx_invitations_expiry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
