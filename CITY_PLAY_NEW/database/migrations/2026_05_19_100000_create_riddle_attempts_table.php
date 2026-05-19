<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riddle_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('riddle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('selected_answer', 255);
            $table->timestamp('qcm_validated_at')->nullable()
                  ->comment('Étape 1 validée : réponse QCM/texte confirmée');
            $table->timestamps();

            $table->unique(['game_session_id', 'riddle_id', 'user_id'], 'uq_riddle_attempts_session_riddle_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riddle_attempts');
    }
};
