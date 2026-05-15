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
        Schema::create('session_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained('game_sessions')->cascadeOnDelete();
            $table->foreignId('riddle_id')->constrained('riddles')->cascadeOnDelete();
            $table->string('user_answer', 255);
            $table->boolean('is_correct')->default(false);
            $table->integer('attempt_number')->default(1);
            $table->integer('response_time_seconds')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->float('distance_from_target')->nullable()->comment('Distance en mètres depuis le lieu cible');
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_answers');
    }
};
