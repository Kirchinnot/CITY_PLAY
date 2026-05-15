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
        Schema::create('riddles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_id')->constrained('places')->cascadeOnDelete();
            $table->string('title', 150)->nullable();
            $table->text('question')->nullable();
            $table->enum('difficulty', ['child', 'force_1', 'force_2', 'force_3']);
            $table->integer('points')->default(10);
            $table->integer('time_limit_seconds')->default(120);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riddles');
    }
};
