<?php

namespace Database\Seeders;

use App\Models\Filiere;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Prix en FCFA : [mensuel, trimestriel, annuel]
        $tarifs = [
            'economie' => [2500, 6500, 22000],
            'industrie' => [3500, 9000, 30000],
            'informatique' => [5000, 13000, 45000],
        ];

        $durees = [
            ['nom' => 'Mensuel', 'jours' => 30],
            ['nom' => 'Trimestriel', 'jours' => 90],
            ['nom' => 'Annuel', 'jours' => 365],
        ];

        foreach ($tarifs as $slug => $prix) {
            $filiere = Filiere::where('slug', $slug)->firstOrFail();

            foreach ($durees as $i => $duree) {
                Plan::updateOrCreate(
                    ['filiere_id' => $filiere->id, 'duree_jours' => $duree['jours']],
                    ['nom' => $duree['nom'], 'prix' => $prix[$i], 'actif' => true]
                );
            }
        }
    }
}