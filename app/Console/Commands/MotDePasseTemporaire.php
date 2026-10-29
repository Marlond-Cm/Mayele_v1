<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MotDePasseTemporaire extends Command
{
    protected $signature = 'mayele:mot-de-passe {identifiant : E-mail ou téléphone du compte}';

    protected $description = 'Génère un mot de passe temporaire pour un étudiant qui n\'a plus accès à son e-mail.';

    public function handle(): int
    {
        $identifiant = trim((string) $this->argument('identifiant'));

        $user = User::where('email', $identifiant)
            ->orWhere('telephone', preg_replace('/\s+/', '', $identifiant))
            ->first();

        if ($user === null) {
            $this->error('Aucun compte trouvé.');

            return self::FAILURE;
        }

        $motDePasse = Str::password(10, symbols: false);

        $user->forceFill(['password' => $motDePasse, 'remember_token' => Str::random(60)])->save();
        DB::table('sessions')->where('user_id', $user->id)->delete();

        $this->info("Compte : {$user->name} ({$user->email})");
        $this->info("Mot de passe temporaire : {$motDePasse}");
        $this->line('Transmets-le à l\'étudiant par un canal privé et conseille-lui de le remplacer avec « Mot de passe oublié ».');

        return self::SUCCESS;
    }
}