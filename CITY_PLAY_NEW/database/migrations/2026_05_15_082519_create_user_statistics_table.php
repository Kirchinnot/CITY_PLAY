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
        Schema::create('user_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->integer('total_games')->default(0);
            $table->integer('total_score')->default(0);
            $table->integer('total_riddles_solved')->default(0);
            $table->integer('total_riddles_failed')->default(0);
            $table->float('total_distance_walked')->default(0)->comment('En kilomètres');
            $table->float('average_response_time')->default(0)->comment('En secondes');
            $table->float('success_rate')->default(0)->comment('Pourcentage 0-100');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_statistics');
    }
};
