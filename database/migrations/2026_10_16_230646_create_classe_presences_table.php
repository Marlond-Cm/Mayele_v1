<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists('classe_presences');
    }
};