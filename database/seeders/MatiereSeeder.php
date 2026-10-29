<?php

namespace Database\Seeders;

use App\Models\Filiere;
use App\Models\Matiere;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MatiereSeeder extends Seeder
{
    /** Matières ouvertes en premier (MVP). Les autres restent masquées. */
    private const MVP = [
        'Comptabilité générale 1',
        'Comptabilité générale 2',
        'Microéconomie',
        'Statistiques descriptives',
        'Mathématiques financières',
        'Analyse financière',
        'Droit des affaires OHADA',
    ];

    public function run(): void
    {
        $filiere = Filiere::where('slug', 'economie')->firstOrFail();

        $tronc = [
            ['L1', 1, [
                'Introduction à l\'économie',
                'Comptabilité générale 1',
                'Mathématiques appliquées à la gestion',
                'Introduction au droit',
                'Informatique de bureau (Word, Excel)',
                'Techniques d\'expression et communication',
            ]],
            ['L1', 2, [
                'Microéconomie',
                'Comptabilité générale 2',
                'Statistiques descriptives',
                'Introduction à la gestion des entreprises',
                'Droit civil et commercial',
                'Anglais des affaires',
            ]],
            ['L2', 3, [
                'Macroéconomie',
                'Comptabilité des sociétés (SYSCOHADA)',
                'Probabilités et statistiques inférentielles',
                'Mathématiques financières',
                'Marketing fondamental',
                'Droit des affaires OHADA',
            ]],
            ['L2', 4, [
                'Économie monétaire et financière',
                'Comptabilité analytique',
                'Analyse financière',
                'Management des organisations',
                'Fiscalité',
                'Excel avancé et tableur de gestion',
            ]],
        ];

        foreach ($tronc as [$niveau, $semestre, $matieres]) {
            foreach ($matieres as $ordre => $nom) {
                $this->creer($filiere, $niveau, $semestre, null, $nom, $ordre + 1);
            }
        }

        $parcours = [
            'Comptabilité-Finance' => [
                'Audit et contrôle de gestion',
                'Gestion financière approfondie',
                'Comptabilité approfondie et consolidation',
                'Fiscalité des entreprises',
                'Marchés financiers',
                'Mémoire et stage',
            ],
            'Gestion / Management' => [
                'GRH',
                'Gestion de projet',
                'Stratégie d\'entreprise',
                'Entrepreneuriat et business plan',
                'Gestion de la production et qualité',
                'Mémoire et stage',
            ],
            'Marketing-Commerce' => [
                'Marketing digital',
                'Comportement du consommateur',
                'Études de marché',
                'Techniques de vente et négociation',
                'Communication commerciale',
                'Mémoire et stage',
            ],
            'Économie' => [
                'Économie du développement',
                'Économie internationale et CEMAC',
                'Économétrie',
                'Politiques économiques',
                'Finances publiques',
                'Mémoire et stage',
            ],
        ];

        foreach ($parcours as $nomParcours => $matieres) {
            foreach ($matieres as $ordre => $nom) {
                // 3 premières matières au semestre 5, les 3 suivantes au semestre 6
                $semestre = $ordre < 3 ? 5 : 6;
                $this->creer($filiere, 'L3', $semestre, $nomParcours, $nom, $ordre + 1);
            }
        }
    }

    private function creer(Filiere $filiere, string $niveau, int $semestre, ?string $parcours, string $nom, int $ordre): void
    {
        $slug = Str::slug(implode(' ', array_filter([$nom, $niveau, $parcours])));

        Matiere::updateOrCreate(
            ['slug' => $slug],
            [
                'filiere_id' => $filiere->id,
                'niveau' => $niveau,
                'semestre' => $semestre,
                'parcours' => $parcours,
                'nom' => $nom,
                'ordre' => $ordre,
                'publie' => in_array($nom, self::MVP, true),
            ]
        );
    }
}