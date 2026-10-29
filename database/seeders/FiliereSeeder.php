<?php

namespace Database\Seeders;

use App\Models\Filiere;
use Illuminate\Database\Seeder;

class FiliereSeeder extends Seeder
{
    public function run(): void
    {
        $filieres = [
            [
                'nom' => 'Économie & Gestion',
                'slug' => 'economie',
                'theme' => 'economie',
                'description' => 'Comptabilité, finance, marketing, droit des affaires et économie : réussis ta licence avec des cours alignés sur ton programme.',
                'actif' => true,
                'ordre' => 1,
            ],
            [
                'nom' => 'Industrie & Ingénierie',
                'slug' => 'industrie',
                'theme' => 'industrie',
                'description' => 'RDM, électrotechnique, automatisme, maintenance et dessin technique.',
                'actif' => true,
                'ordre' => 2,
            ],
            [
                'nom' => 'Informatique & Développement',
                'slug' => 'informatique',
                'theme' => 'informatique',
                'description' => 'Algorithmique, programmation, bases de données, réseaux, web et mobile.',
                'actif' => true,
                'ordre' => 3,
            ],
        ];

        foreach ($filieres as $filiere) {
            Filiere::updateOrCreate(['slug' => $filiere['slug']], $filiere);
        }
    }
}