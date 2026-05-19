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
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->index('status');
            $table->index('host_user_id');
        });

        Schema::table('session_places', function (Blueprint $table) {
            $table->index(['game_session_id', 'is_completed']);
            $table->index('order_index');
        });

        Schema::table('scores', function (Blueprint $table) {
            $table->index(['game_session_id', 'user_id']);
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropIndex(['token']);
        });

        Schema::table('scores', function (Blueprint $table) {
            $table->dropIndex(['game_session_id', 'user_id']);
        });

        Schema::table('session_places', function (Blueprint $table) {
            $table->dropIndex(['game_session_id', 'is_completed']);
            $table->dropIndex(['order_index']);
        });

        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['host_user_id']);
        });
    }
};
