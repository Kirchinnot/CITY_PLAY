<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE : users
 * Rôle  : Stocke tous les comptes (admins mairie et joueurs).
 *         C'est la table racine de toute la base — tout FK users.id part d'ici.
 *
 * Liens :
 *   → cities.created_by
 *   → invitations.created_by
 *   → game_sessions.host_user_id
 *   → game_players.user_id
 *   → scores.user_id (NULL en mode collectif)
 *   → achievements.user_id
 *   → notifications.notifiable_id (morphable)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            // ── Identité ──────────────────────────────────────────────────
            $table->id();
            $table->string('name', 150);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable()
                  ->comment('Optionnel — utile pour notifications SMS');

            // ── Rôle & sécurité ───────────────────────────────────────────
            $table->enum('role', ['admin', 'player'])->default('player')
                  ->comment('admin = mairie/créateur, player = joueur');
            $table->string('avatar', 255)->nullable()
                  ->comment('Chemin Storage vers la photo de profil');
            $table->boolean('two_factor_enabled')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            
            // ── RGPD & CGU ────────────────────────────────────────────────
            $table->timestamp('cgu_accepted_at')->nullable();
            $table->timestamp('privacy_policy_accepted_at')->nullable();
            $table->boolean('delete_requested')->default(false)
                  ->comment('TRUE si l\'utilisateur a demandé la suppression de son compte');

            $table->rememberToken();

            $table->timestamps();
        });

        // Table de réinitialisation de mot de passe (Laravel standard)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Table de sessions Laravel (auth web)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
