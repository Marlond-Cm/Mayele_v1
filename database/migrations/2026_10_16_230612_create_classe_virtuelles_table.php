<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes_virtuelles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filiere_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matiere_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('animateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('salle')->unique();             // nom secret de la salle de visio
            $table->dateTime('debut_le');
            $table->unsignedSmallInteger('duree_minutes')->default(60);
            $table->boolean('est_gratuit')->default(false);
            $table->string('replay_url', 500)->nullable();
            $table->string('statut', 12)->default('planifie'); // planifie | annule
            $table->timestamps();

            $table->index(['filiere_id', 'debut_le']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes_virtuelles');
    }
};