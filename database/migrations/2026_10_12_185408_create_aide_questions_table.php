<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aide_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visiteur', 60);                    // u{id} ou s{hash de session}
            $table->text('question');
            $table->text('reponse')->nullable();
            $table->string('source', 10)->default('faq');      // ia | faq
            $table->boolean('besoin_support')->default(false);
            $table->boolean('utile')->nullable();              // avis 👍 / 👎
            $table->unsignedInteger('tokens_entree')->default(0);
            $table->unsignedInteger('tokens_sortie')->default(0);
            $table->timestamps();

            $table->index(['visiteur', 'created_at']);
            $table->index(['source', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aide_questions');
    }
};