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
        Schema::create('session_places', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained('game_sessions')->cascadeOnDelete();
            $table->foreignId('place_id')->constrained('places')->cascadeOnDelete();
            $table->foreignId('riddle_id')->constrained('riddles')->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->integer('attempts_count')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_places');
    }
};
