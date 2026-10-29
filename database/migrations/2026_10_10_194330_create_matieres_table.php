<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matieres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filiere_id')->constrained()->cascadeOnDelete();
            $table->string('niveau', 10);                 // L1, L2, L3
            $table->unsignedTinyInteger('semestre');      // 1 à 6
            $table->string('parcours')->nullable();       // L3 : Comptabilité-Finance, etc.
            $table->string('nom');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->boolean('publie')->default(false);
            $table->timestamps();

            $table->index(['filiere_id', 'niveau', 'semestre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matieres');
    }
};