<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abonnements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->timestamp('debut_le')->nullable();
            $table->timestamp('fin_le')->nullable();
            $table->string('statut')->default('en_attente'); // en_attente | actif | expire | annule
            $table->unsignedInteger('montant');
            $table->timestamps();

            $table->index(['user_id', 'statut', 'fin_le']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abonnements');
    }
};