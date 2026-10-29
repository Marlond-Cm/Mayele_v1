<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filiere_id')->constrained()->cascadeOnDelete();
            $table->string('nom');                       // Mensuel, Trimestriel, Annuel
            $table->unsignedSmallInteger('duree_jours'); // 30, 90, 365
            $table->unsignedInteger('prix');             // en FCFA
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->unique(['filiere_id', 'duree_jours']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};