<?php

namespace Database\Seeders;

use App\Models\Universite;
use Illuminate\Database\Seeder;

class UniversiteSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->universites() as [$nom, $sigle, $slug, $ville, $type]) {
            Universite::updateOrCreate(
                ['slug' => $slug],
                ['nom' => $nom, 'sigle' => $sigle, 'ville' => $ville, 'type' => $type, 'actif' => true]
            );
        }
    }

    /** @return array<int, array{0: string, 1: ?string, 2: string, 3: string, 4: string}> */
    private function universites(): array
    {
        return [
            // Publiques
            ['Université Marien Ngouabi', 'UMNG', 'umng', 'Brazzaville', 'public'],
            ['Université Denis Sassou-Nguesso', 'UDSN', 'udsn', 'Kintélé', 'public'],

            // Privées : Pointe-Noire
            ['Sueco', 'SUECO', 'sueco', 'Pointe-Noire', 'prive'],
            ['École supérieure de commerce et d\'industrie du Congo', 'ESCIC', 'escic', 'Pointe-Noire', 'prive'],
            ['Institut International 2i – Pointe-Noire', '2i', '2i-pointe-noire', 'Pointe-Noire', 'prive'],
            ['École supérieure de technologie du Littoral', 'EST-Littoral', 'est-littoral', 'Pointe-Noire', 'prive'],
            ['Université de Loango', 'UL', 'universite-de-loango', 'Pointe-Noire', 'prive'],
            ['Institut UCAC-ICAM – Pointe-Noire', 'UCAC-ICAM', 'ucac-icam-pointe-noire', 'Pointe-Noire', 'prive'],
            ['CEREC-ISCOM', 'CEREC-ISCOM', 'cerec-iscom', 'Pointe-Noire', 'prive'],                         // à vérifier
            ['ASMT – École supérieure de management et de technologies', 'ASMT', 'asmt', 'Pointe-Noire', 'prive'], // à vérifier
            ['École africaine de développement – Pointe-Noire', 'EAD-PN', 'ead-pointe-noire', 'Pointe-Noire', 'prive'],
            ['École canadienne de management', null, 'ecole-canadienne-de-management', 'Pointe-Noire', 'prive'],   // à vérifier
            ['École supérieure du commerce et de gestion (DGC formation)', 'ESCG', 'escg-dgc-formation', 'Pointe-Noire', 'prive'],
            ['Institut africain de technologie', 'IAT', 'iat', 'Pointe-Noire', 'prive'],                           // à vérifier
            ['Institut d\'enseignement supérieur et professionnel', 'IESP', 'iesp', 'Pointe-Noire', 'prive'],      // à vérifier
            ['Institution de gestion et de développement économique', 'IGDE', 'igde', 'Pointe-Noire', 'prive'],    // à vérifier
            ['Institut supérieur de comptabilité', null, 'institut-superieur-de-comptabilite', 'Pointe-Noire', 'prive'], // à vérifier

            // Privées : Brazzaville
            ['École supérieure de gestion et d\'administration des entreprises', 'ESGAE', 'esgae', 'Brazzaville', 'prive'],
            ['Haute École de Gestion', 'HEG-Brazza', 'heg-brazza', 'Brazzaville', 'prive'],
            ['Université Libre du Congo', 'ULC', 'ulc', 'Brazzaville', 'prive'],
            ['Institut International 2i – Brazzaville', '2i', '2i-brazzaville', 'Brazzaville', 'prive'],
            ['IHEM-ISTI', 'IHEM-ISTI', 'ihem-isti', 'Brazzaville', 'prive'],                                   // à vérifier
            ['École africaine de développement – Brazzaville', 'EAD-BZV', 'ead-brazzaville', 'Brazzaville', 'prive'],
            ['Institut Universitaire de Brazzaville', null, 'institut-universitaire-de-brazzaville', 'Brazzaville', 'prive'],
            ['Institut Universitaire de Technologie d\'Afrique Centrale', 'IUTAC', 'iutac', 'Brazzaville', 'prive'], // à vérifier
            ['Institut Supérieur Polytechnique-Université', 'ISP-U', 'isp-u', 'Brazzaville', 'prive'],           // à vérifier
            ['Hautes Études Commerciales et Techniques', 'HECT', 'hect', 'Brazzaville', 'prive'],               // à vérifier
            ['Management Institute of Brazzaville', null, 'management-institute-of-brazzaville', 'Brazzaville', 'prive'], // à vérifier
            ['École africaine d\'excellence', 'EAE', 'eae', 'Brazzaville', 'prive'],                             // à vérifier
            ['Université Internationale de Brazzaville', 'UIB', 'uib', 'Brazzaville', 'prive'],                  // à vérifier
        ];
    }
}