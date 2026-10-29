<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_tentatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->unsignedSmallInteger('bonnes_reponses')->default(0);
            $table->unsignedSmallInteger('total_questions')->default(0);
            $table->json('reponses')->nullable();
            $table->timestamp('termine_le')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'quiz_id']);
            $table->index('termine_le');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_tentatives');
    }
};