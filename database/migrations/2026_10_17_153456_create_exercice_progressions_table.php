<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercice_progressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->mediumText('code')->nullable();
            $table->unsignedSmallInteger('tentatives')->default(0);
            $table->boolean('reussi')->default(false);
            $table->timestamp('reussi_le')->nullable();
            $table->boolean('solution_vue')->default(false);
            $table->timestamps();

            $table->unique(['exercice_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercice_progressions');
    }
};