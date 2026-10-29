<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $parentOk = Schema::hasTable('classes_virtuelles') && Schema::hasColumn('classes_virtuelles', 'salle');

        if (! $parentOk) {
            // Tables créées sous un mauvais nom ou sans leurs colonnes : elles ne contiennent aucune donnée
            Schema::dropIfExists('classe_presences');
            Schema::dropIfExists('classe_virtuelles');
            Schema::dropIfExists('classes_virtuelles');

            Schema::create('classes_virtuelles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('filiere_id')->constrained()->cascadeOnDelete();
                $table->foreignId('matiere_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('animateur_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('titre');
                $table->text('description')->nullable();
                $table->string('salle')->unique();
                $table->dateTime('debut_le');
                $table->unsignedSmallInteger('duree_minutes')->default(60);
                $table->boolean('est_gratuit')->default(false);
                $table->string('replay_url', 500)->nullable();
                $table->string('statut', 12)->default('planifie');
                $table->timestamps();

                $table->index(['filiere_id', 'debut_le']);
            });
        }

        if (! Schema::hasTable('classe_presences') || ! Schema::hasColumn('classe_presences', 'classe_virtuelle_id')) {
            Schema::dropIfExists('classe_presences');

            Schema::create('classe_presences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classe_virtuelle_id')->constrained('classes_virtuelles')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamp('premiere_entree_at');
                $table->timestamp('derniere_presence_at');
                $table->timestamps();

                $table->unique(['classe_virtuelle_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        // Volontairement vide : cette migration ne fait que réparer.
    }
};