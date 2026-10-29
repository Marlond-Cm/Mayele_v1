<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coach_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('coach_conversations')->cascadeOnDelete();
            $table->string('role', 10); // user | assistant
            $table->text('contenu');
            $table->unsignedInteger('tokens_entree')->default(0);
            $table->unsignedInteger('tokens_sortie')->default(0);
            $table->timestamps();

            $table->index(['role', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_messages');
    }
};