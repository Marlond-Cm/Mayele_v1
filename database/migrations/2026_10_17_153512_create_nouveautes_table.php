<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nouveautes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filiere_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('titre');
            $table->string('lien', 300);
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nouveautes');
    }
};