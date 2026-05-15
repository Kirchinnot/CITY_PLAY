<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE : notifications
 * Rôle  : Notifications in-app au format standard Laravel.
 *         Polled par Dev 3 toutes les 5s via GET /api/notifications/unread
 *         et affichées comme toasts (vue-toastification).
 *
 *         Types de notifications :
 *           - App\Notifications\RiddleSolvedNotification    (énigme résolue)
 *           - App\Notifications\PlaceUnlockedNotification   (nouveau lieu débloqué)
 *           - App\Notifications\HintUsedNotification        (indice utilisé, points déduits)
 *           - App\Notifications\SessionCompletedNotification (fin de partie)
 *           - App\Notifications\BadgeEarnedNotification     (badge décroché)
 *
 *         La colonne `data` est un JSON contenant le contenu du toast :
 *           { "message": "Bravo ! +150 pts", "type": "success", "points": 150 }
 *
 *         notifiable_type / notifiable_id : système morphable Laravel.
 *         Pour Cityplay : notifiable_type = "App\Models\User", notifiable_id = user.id
 *
 * Note : cette table est générée automatiquement par
 *        php artisan notifications:table
 *        On la documente ici pour cohérence avec l'équipe.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {

            $table->uuid('id')->primary()
                  ->comment('UUID Laravel — format standard des notifications');
            $table->string('type')
                  ->comment('Classe de notification : App\Notifications\RiddleSolvedNotification');
            $table->morphs('notifiable');
            $table->text('data')
                  ->comment('JSON : {message, type, points, badge_type, place_name, ...}');
            $table->timestamp('read_at')->nullable()
                  ->comment('NULL = non lue. Mis à jour par POST /api/notifications/mark-read');
            $table->timestamps();

            // ── Index pour le polling ─────────────────────────────────────
            $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'idx_notif_notifiable_unread');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
