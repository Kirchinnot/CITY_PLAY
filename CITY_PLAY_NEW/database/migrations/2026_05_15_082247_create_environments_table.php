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
        Schema::create('environments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('city', 100);
            $table->string('country', 100);
            $table->string('description', 500)->nullable();
            $table->string('cover_image', 255)->nullable();
            $table->integer('retention_days')->default(365)->comment('Durée de conservation des données utilisateur en jours');
            $table->boolean('is_published')->default(false);
            $table->text('conclusion')->nullable()->comment('Message de fin paramétrable par la mairie');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('environments');
    }
};
