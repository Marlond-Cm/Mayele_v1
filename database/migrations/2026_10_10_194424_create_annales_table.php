<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matiere_id')->constrained()->cascadeOnDelete();
            $table->foreignId('universite_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // contributeur
            $table->unsignedSmallInteger('annee');
            $table->string('session')->default('normale'); // normale | rattrapage
            $table->string('type')->default('examen');     // examen | partiel | td
            $table->string('fichier_sujet');
            $table->string('fichier_corrige')->nullable();
            $table->string('statut')->default('en_attente'); // en_attente | valide | refuse
            $table->timestamps();

            $table->index(['matiere_id', 'universite_id', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annales');
    }
};