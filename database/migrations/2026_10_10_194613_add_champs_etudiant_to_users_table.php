<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telephone')->nullable()->unique()->after('email');
            $table->foreignId('universite_id')->nullable()->after('telephone')
                ->constrained()->nullOnDelete();
            $table->foreignId('filiere_id')->nullable()->after('universite_id')
                ->constrained()->nullOnDelete();
            $table->string('niveau', 10)->nullable()->after('filiere_id'); // L1, L2, L3
            $table->string('role')->default('etudiant')->after('niveau');  // etudiant | tuteur | admin
            $table->string('code_parrainage')->nullable()->unique()->after('role');
            $table->foreignId('parrain_id')->nullable()->after('code_parrainage')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parrain_id');
            $table->dropConstrainedForeignId('filiere_id');
            $table->dropConstrainedForeignId('universite_id');
            $table->dropColumn(['telephone', 'niveau', 'role', 'code_parrainage']);
        });
    }
};