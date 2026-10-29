<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapitres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matiere_id')->constrained()->cascadeOnDelete();
            $table->string('titre');
            $table->string('slug');
            $table->longText('contenu')->nullable();
            $table->string('video_url')->nullable();
            $table->string('fichier_pdf')->nullable();
            $table->unsignedSmallInteger('duree_minutes')->default(0);
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->boolean('est_gratuit')->default(false); // 1er chapitre ouvert à tous
            $table->boolean('publie')->default(false);
            $table->timestamps();

            $table->unique(['matiere_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapitres');
    }
};