<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapitre_id')->constrained()->cascadeOnDelete();
            $table->timestamp('termine_le')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'chapitre_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progressions');
    }
};