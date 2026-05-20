<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_players', function (Blueprint $table) {
            $table->foreignId('validated_riddle_id')
                ->nullable()
                ->after('current_riddle_id')
                ->constrained('riddles')
                ->nullOnDelete()
                ->comment('Énigme dont la réponse texte a été validée, en attente de confirmation GPS');

            $table->timestamp('answer_validated_at')
                ->nullable()
                ->after('validated_riddle_id')
                ->comment('Moment de la validation de la réponse (avant déplacement)');
        });
    }

    public function down(): void
    {
        Schema::table('game_players', function (Blueprint $table) {
            $table->dropForeign(['validated_riddle_id']);
            $table->dropColumn(['validated_riddle_id', 'answer_validated_at']);
        });
    }
};
