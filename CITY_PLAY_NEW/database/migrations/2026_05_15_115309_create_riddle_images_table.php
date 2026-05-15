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
        Schema::create('riddle_images', function (Blueprint $table) {

            $table->id();
            $table->foreignId('riddle_id')
                  ->constrained('riddles')
                  ->cascadeOnDelete()
                  ->comment('Énigme à laquelle appartient cette image');
            $table->string('image_url', 255)
                  ->comment("Chemin Storage ou URL — image d'illustration de l'énigme");
            $table->integer('display_order')->default(1)
                  ->comment("Ordre d'affichage : 1 = image principale");
            $table->string('caption', 255)->nullable()
                  ->comment('Légende optionnelle — affiché sous la photo dans RiddleCard.vue');

            $table->timestamp('created_at')->nullable();

            // ── Index ─────────────────────────────────────────────────────
            $table->index(['riddle_id', 'display_order'], 'idx_riddle_images_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riddle_images');
    }
};
