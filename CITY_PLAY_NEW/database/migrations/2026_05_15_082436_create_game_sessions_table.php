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
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('environment_id')->constrained('environments')->cascadeOnDelete();
            $table->foreignId('current_place_id')->nullable()->constrained('places')->nullOnDelete();
            $table->foreignId('current_riddle_id')->nullable()->constrained('riddles')->nullOnDelete();
            $table->enum('transport_mode', ['walking', 'bike', 'car']);
            $table->enum('difficulty', ['child', 'force_1', 'force_2', 'force_3']);
            $table->integer('available_minutes');
            $table->integer('score')->default(0);
            $table->integer('total_riddles')->default(0);
            $table->integer('solved_riddles')->default(0);
            $table->integer('failed_riddles')->default(0);
            $table->enum('status', ['active', 'paused', 'completed', 'abandoned'])->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};
