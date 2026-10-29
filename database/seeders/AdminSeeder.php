<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $motDePasse = env('ADMIN_PASSWORD');

        if (empty($email) || empty($motDePasse)) {
            $this->command?->warn('ADMIN_EMAIL ou ADMIN_PASSWORD manquant dans .env : administrateur non créé.');

            return;
        }

        $admin = User::firstOrNew(['email' => $email]);
        $admin->name = 'Administrateur Mayele';
        $admin->password = $motDePasse; // haché automatiquement par le cast
        $admin->role = 'admin';          // non mass-assignable, donc assigné directement
        $admin->email_verified_at = now();
        $admin->save();
    }
}