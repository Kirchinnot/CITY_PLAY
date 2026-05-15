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
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->foreignId('environment_id')->constrained('environments')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('description', 500)->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('validation_radius')->default(30)->comment('Rayon de validation en mètres');
            $table->integer('estimated_visit_minutes')->default(10);
            $table->integer('visit_order')->default(1)->comment('Classement par popularité défini par la mairie');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
