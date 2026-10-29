<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recompenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);                  // parrainage | annale
            $table->unsignedBigInteger('source_id');     // id du filleul ou de l'annale
            $table->unsignedSmallInteger('jours');
            $table->foreignId('abonnement_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            // Une récompense par filleul / par annale, jamais deux
            $table->unique(['user_id', 'type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recompenses');
    }
};