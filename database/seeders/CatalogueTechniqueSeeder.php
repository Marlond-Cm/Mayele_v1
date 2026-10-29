<?php

namespace Database\Seeders;

use App\Models\Filiere;
use App\Models\Matiere;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogueTechniqueSeeder extends Seeder
{
    public function run(): void
    {
        $this->importer('industrie', $this->industrie());
        $this->importer('informatique', $this->informatique());
    }

    private function importer(string $slugFiliere, array $catalogue): void
    {
        $filiere = Filiere::where('slug', $slugFiliere)->firstOrFail();

        foreach ($catalogue['tronc'] as [$niveau, $semestre, $matieres]) {
            foreach ($matieres as $ordre => $nom) {
                $this->creer($filiere, $niveau, $semestre, null, $nom, $ordre + 1);
            }
        }

        foreach ($catalogue['parcours'] as $parcours => $matieres) {
            foreach ($matieres as $ordre => $nom) {
                $this->creer($filiere, 'L3', $ordre < 3 ? 5 : 6, $parcours, $nom, $ordre + 1);
            }
        }
    }

    private function creer(Filiere $filiere, string $niveau, int $semestre, ?string $parcours, string $nom, int $ordre): void
    {
        $slug = Str::slug(implode(' ', array_filter([$filiere->slug, $nom, $niveau, $parcours])));

        // firstOrCreate : un nouveau passage du seeder n'écrase pas tes réglages (publie, etc.)
        Matiere::firstOrCreate(
            ['slug' => $slug],
            [
                'filiere_id' => $filiere->id,
                'niveau' => $niveau,
                'semestre' => $semestre,
                'parcours' => $parcours,
                'nom' => $nom,
                'ordre' => $ordre,
                'publie' => $niveau === 'L1', // le reste se publie depuis l'admin
            ]
        );
    }

    private function industrie(): array
    {
        return [
            'tronc' => [
                ['L1', 1, ['Mathématiques 1', 'Physique 1 (mécanique)', 'Chimie générale', 'Dessin technique', 'Initiation à l\'informatique', 'Techniques d\'expression et communication']],
                ['L1', 2, ['Mathématiques 2', 'Physique 2 (électricité)', 'Électricité générale', 'Mécanique générale', 'Technologie des matériaux', 'Anglais technique']],
                ['L2', 3, ['Thermodynamique', 'Résistance des matériaux', 'Électrotechnique', 'Électronique analogique', 'Mécanique des fluides', 'Dessin assisté par ordinateur (AutoCAD)']],
                ['L2', 4, ['Automatisme et API', 'Machines électriques', 'Fabrication mécanique', 'Maintenance industrielle', 'Métrologie et contrôle qualité', 'Hygiène, sécurité, environnement (HSE)']],
            ],
            'parcours' => [
                'Génie électrique' => ['Électronique de puissance', 'Réseaux électriques', 'Énergies renouvelables', 'Régulation et asservissement', 'Installations électriques industrielles', 'Mémoire et stage'],
                'Génie mécanique et maintenance' => ['Conception mécanique (CAO)', 'Maintenance préventive et GMAO', 'Transferts thermiques', 'Hydraulique et pneumatique', 'Gestion de production', 'Mémoire et stage'],
                'Génie industriel et procédés' => ['Procédés industriels', 'Instrumentation et mesures', 'Sécurité des procédés', 'Logistique industrielle', 'Management de la qualité', 'Mémoire et stage'],
            ],
        ];
    }

    private function informatique(): array
    {
        return [
            'tronc' => [
                ['L1', 1, ['Algorithmique 1', 'Initiation à la programmation (C)', 'Mathématiques discrètes', 'Architecture des ordinateurs', 'Systèmes d\'exploitation', 'Bureautique et culture numérique']],
                ['L1', 2, ['Algorithmique 2 et structures de données', 'Programmation C avancée', 'Introduction aux bases de données', 'Réseaux informatiques 1', 'Algèbre linéaire', 'Anglais technique']],
                ['L2', 3, ['Programmation orientée objet (Java)', 'Bases de données SQL', 'Développement web (HTML, CSS, JavaScript)', 'Réseaux informatiques 2', 'Administration Linux', 'Analyse et conception UML']],
                ['L2', 4, ['PHP et Laravel', 'Structures de données avancées', 'Génie logiciel', 'Programmation mobile', 'Sécurité informatique', 'Python']],
            ],
            'parcours' => [
                'Développement logiciel' => ['Architecture logicielle', 'API REST et services web', 'Développement mobile avancé', 'DevOps (Git, Docker)', 'Tests et qualité logicielle', 'Mémoire et stage'],
                'Réseaux et sécurité' => ['Administration des réseaux', 'Cybersécurité', 'Cloud computing', 'Virtualisation', 'Supervision et haute disponibilité', 'Mémoire et stage'],
                'Data et intelligence artificielle' => ['Data science avec Python', 'Machine learning', 'Big data', 'Business intelligence', 'Statistiques pour la data', 'Mémoire et stage'],
            ],
        ];
    }
}