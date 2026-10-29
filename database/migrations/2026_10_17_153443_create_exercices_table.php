<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filiere_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matiere_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('chapitre_id')->nullable()->constrained()->nullOnDelete();
            $table->string('titre');
            $table->string('slug')->unique();
            $table->text('enonce');
            $table->string('langage', 12);               // python | javascript | sql | html
            $table->text('code_depart')->nullable();
            $table->json('tests');
            $table->text('indice')->nullable();
            $table->text('solution')->nullable();
            $table->string('difficulte', 10)->default('facile');
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->boolean('est_gratuit')->default(false);
            $table->boolean('publie')->default(false);
            $table->timestamps();

            $table->index(['filiere_id', 'publie']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercices');
    }
};