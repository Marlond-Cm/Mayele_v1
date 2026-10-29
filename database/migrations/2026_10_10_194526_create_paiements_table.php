<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('abonnement_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();            // notre référence interne
            $table->string('reference_fournisseur')->nullable(); // référence GeniusPay
            $table->unsignedInteger('montant');
            $table->string('devise', 3)->default('XAF');
            $table->string('operateur')->nullable();          // mtn | airtel
            $table->string('telephone')->nullable();
            $table->string('statut')->default('en_attente');  // en_attente | reussi | echoue
            $table->json('reponse_brute')->nullable();
            $table->timestamp('paye_le')->nullable();
            $table->timestamps();

            $table->index(['statut', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};