<?php

namespace Database\Seeders;

use App\Models\Exercice;
use App\Models\Filiere;
use App\Models\Matiere;
use Illuminate\Database\Seeder;

class ExerciceSeeder extends Seeder
{
    public function run(): void
    {
        $filiere = Filiere::where('slug', 'informatique')->first();

        if ($filiere === null) {
            $this->command?->warn('Filière « informatique » introuvable : seeder ignoré.');

            return;
        }

        $python = Matiere::where('slug', 'informatique-python-l2')->value('id');
        $sql = Matiere::where('slug', 'informatique-bases-de-donnees-sql-l2')->value('id');
        $web = Matiere::where('slug', 'informatique-developpement-web-html-css-javascript-l2')->value('id');

        $base = "CREATE TABLE etudiants (id INTEGER PRIMARY KEY, nom TEXT, filiere TEXT, note REAL);\n"
            ."INSERT INTO etudiants VALUES (1,'Marie','Informatique',15),(2,'Paul','Economie',13),(3,'Jean','Informatique',9),(4,'Aline','Informatique',12),(5,'Brice','Industrie',16);";

        $table7 = implode("\n", array_map(fn ($i) => "7 x {$i} = ".(7 * $i), range(1, 10)));
        $table3 = implode("\n", array_map(fn ($i) => "3 x {$i} = ".(3 * $i), range(1, 10)));

        $exercices = [
            [
                'titre' => 'Dire bonjour', 'slug' => 'python-dire-bonjour', 'langage' => 'python', 'difficulte' => 'facile', 'matiere_id' => $python, 'gratuit' => true,
                'enonce' => "Lis le prénom d'une personne avec `input()` et affiche `Bonjour, <prénom> !`.\n\n**Exemple** : si l'entrée est `Marie`, le programme affiche `Bonjour, Marie !`.",
                'depart' => "nom = input()\n# Écris la suite ici\n",
                'indice' => "Utilise `print()` avec une f-string : `print(f\"Bonjour, {nom} !\")`.",
                'solution' => "nom = input()\nprint(f\"Bonjour, {nom} !\")",
                'tests' => [
                    ['type' => 'io', 'entree' => 'Marie', 'sortie' => 'Bonjour, Marie !', 'visible' => true],
                    ['type' => 'io', 'entree' => 'Paul', 'sortie' => 'Bonjour, Paul !', 'visible' => true],
                    ['type' => 'io', 'entree' => 'Aline', 'sortie' => 'Bonjour, Aline !', 'visible' => false],
                ],
            ],
            [
                'titre' => 'Additionner deux nombres', 'slug' => 'python-additionner', 'langage' => 'python', 'difficulte' => 'facile', 'matiere_id' => $python, 'gratuit' => false,
                'enonce' => "Lis deux nombres entiers (un par ligne) et affiche leur **somme**.\n\n**Exemple** : entrées `3` puis `5` → le programme affiche `8`.",
                'depart' => "a = int(input())\nb = int(input())\n# Affiche leur somme\n",
                'indice' => "`int()` transforme un texte en nombre entier. Pour additionner : `a + b`.",
                'solution' => "a = int(input())\nb = int(input())\nprint(a + b)",
                'tests' => [
                    ['type' => 'io', 'entree' => "3\n5", 'sortie' => '8', 'visible' => true],
                    ['type' => 'io', 'entree' => "10\n20", 'sortie' => '30', 'visible' => true],
                    ['type' => 'io', 'entree' => "-4\n9", 'sortie' => '5', 'visible' => false],
                ],
            ],
            [
                'titre' => 'Pair ou impair ?', 'slug' => 'python-pair-impair', 'langage' => 'python', 'difficulte' => 'facile', 'matiere_id' => $python, 'gratuit' => false,
                'enonce' => "Lis un nombre entier et affiche `pair` s'il est pair, sinon `impair`.\n\n**Exemples** : `4` → `pair` ; `7` → `impair`.",
                'depart' => "n = int(input())\n# Utilise if / else\n",
                'indice' => "Un nombre est pair quand le reste de sa division par 2 vaut 0 : `n % 2 == 0`.",
                'solution' => "n = int(input())\nif n % 2 == 0:\n    print(\"pair\")\nelse:\n    print(\"impair\")",
                'tests' => [
                    ['type' => 'io', 'entree' => '4', 'sortie' => 'pair', 'visible' => true],
                    ['type' => 'io', 'entree' => '7', 'sortie' => 'impair', 'visible' => true],
                    ['type' => 'io', 'entree' => '0', 'sortie' => 'pair', 'visible' => false],
                ],
            ],
            [
                'titre' => 'Table de multiplication', 'slug' => 'python-table-multiplication', 'langage' => 'python', 'difficulte' => 'facile', 'matiere_id' => $python, 'gratuit' => false,
                'enonce' => "Lis un nombre `n` et affiche sa table de multiplication de 1 à 10, une ligne par produit, au format `n x i = résultat`.\n\n**Exemple** pour `3` : la première ligne est `3 x 1 = 3` et la dernière `3 x 10 = 30`.",
                'depart' => "n = int(input())\n# Utilise une boucle for\n",
                'indice' => "`for i in range(1, 11):` parcourt les nombres de 1 à 10.",
                'solution' => "n = int(input())\nfor i in range(1, 11):\n    print(f\"{n} x {i} = {n * i}\")",
                'tests' => [
                    ['type' => 'io', 'entree' => '3', 'sortie' => $table3, 'visible' => true],
                    ['type' => 'io', 'entree' => '7', 'sortie' => $table7, 'visible' => false],
                ],
            ],
            [
                'titre' => 'Le plus grand nombre', 'slug' => 'python-maximum', 'langage' => 'python', 'difficulte' => 'moyen', 'matiere_id' => $python, 'gratuit' => false,
                'enonce' => "La première ligne contient `n`, le nombre de valeurs. Les `n` lignes suivantes contiennent chacune un entier. Affiche le **plus grand**.\n\n**Exemple** : `3`, `4`, `9`, `2` → `9`.",
                'depart' => "n = int(input())\nnombres = []\n# Lis les n nombres, puis affiche le maximum\n",
                'indice' => "Ajoute chaque nombre lu à la liste avec `nombres.append(...)`, puis utilise `max(nombres)`.",
                'solution' => "n = int(input())\nnombres = [int(input()) for _ in range(n)]\nprint(max(nombres))",
                'tests' => [
                    ['type' => 'io', 'entree' => "3\n4\n9\n2", 'sortie' => '9', 'visible' => true],
                    ['type' => 'io', 'entree' => "4\n-5\n-2\n-9\n-3", 'sortie' => '-2', 'visible' => false],
                    ['type' => 'io', 'entree' => "1\n7", 'sortie' => '7', 'visible' => false],
                ],
            ],
            [
                'titre' => 'Dire bonjour en JavaScript', 'slug' => 'js-dire-bonjour', 'langage' => 'javascript', 'difficulte' => 'facile', 'matiere_id' => $web, 'gratuit' => true,
                'enonce' => "Lis un prénom avec `lire()` et affiche `Bonjour, <prénom> !` avec `console.log()`.\n\n**Exemple** : `Marie` → `Bonjour, Marie !`.",
                'depart' => "const nom = lire();\n// Affiche : Bonjour, <nom> !\n",
                'indice' => "Utilise une chaîne avec des accents graves : `console.log(`Bonjour, \${nom} !`)`.",
                'solution' => 'const nom = lire();'."\n".'console.log(`Bonjour, ${nom} !`);',
                'tests' => [
                    ['type' => 'io', 'entree' => 'Marie', 'sortie' => 'Bonjour, Marie !', 'visible' => true],
                    ['type' => 'io', 'entree' => 'Paul', 'sortie' => 'Bonjour, Paul !', 'visible' => false],
                ],
            ],
            [
                'titre' => 'Compter les voyelles', 'slug' => 'js-compter-voyelles', 'langage' => 'javascript', 'difficulte' => 'moyen', 'matiere_id' => $web, 'gratuit' => false,
                'enonce' => "Lis un mot (en minuscules, sans accent) et affiche le nombre de voyelles parmi `a`, `e`, `i`, `o`, `u`.\n\n**Exemple** : `bonjour` → `3`.",
                'depart' => "const mot = lire();\nlet compteur = 0;\n// Parcours les lettres du mot\n",
                'indice' => "`for (const lettre of mot) { ... }` et `'aeiou'.includes(lettre)`.",
                'solution' => "const mot = lire();\nlet compteur = 0;\nfor (const lettre of mot) {\n  if ('aeiou'.includes(lettre)) compteur++;\n}\nconsole.log(compteur);",
                'tests' => [
                    ['type' => 'io', 'entree' => 'bonjour', 'sortie' => '3', 'visible' => true],
                    ['type' => 'io', 'entree' => 'programmation', 'sortie' => '5', 'visible' => true],
                    ['type' => 'io', 'entree' => 'rythme', 'sortie' => '1', 'visible' => false],
                ],
            ],
            [
                'titre' => 'Étudiants d\'informatique admis', 'slug' => 'sql-etudiants-informatique', 'langage' => 'sql', 'difficulte' => 'facile', 'matiere_id' => $sql, 'gratuit' => true,
                'enonce' => "La table `etudiants` contient : `id`, `nom`, `filiere`, `note`.\n\nÉcris une requête qui affiche le **nom** des étudiants de la filière `Informatique` ayant une note **supérieure ou égale à 12**, triés par ordre alphabétique.",
                'depart' => "-- Écris ta requête ici\nSELECT ",
                'indice' => "`WHERE filiere = 'Informatique' AND note >= 12` puis `ORDER BY nom`.",
                'solution' => "SELECT nom FROM etudiants WHERE filiere = 'Informatique' AND note >= 12 ORDER BY nom;",
                'tests' => [
                    ['type' => 'sql', 'preparation' => $base, 'attendu' => [['Aline'], ['Marie']], 'ordre' => true, 'visible' => true],
                ],
            ],
            [
                'titre' => 'Moyenne par filière', 'slug' => 'sql-moyenne-par-filiere', 'langage' => 'sql', 'difficulte' => 'moyen', 'matiere_id' => $sql, 'gratuit' => false,
                'enonce' => "Avec la même table `etudiants`, affiche pour chaque **filière** sa **moyenne** des notes, triée par nom de filière.\n\nLe résultat doit avoir deux colonnes : la filière puis la moyenne.",
                'depart' => "-- Écris ta requête ici\nSELECT ",
                'indice' => "Utilise `AVG(note)` avec `GROUP BY filiere`.",
                'solution' => "SELECT filiere, AVG(note) FROM etudiants GROUP BY filiere ORDER BY filiere;",
                'tests' => [
                    ['type' => 'sql', 'preparation' => $base, 'attendu' => [['Economie', '13'], ['Industrie', '16'], ['Informatique', '12']], 'ordre' => true, 'visible' => true],
                ],
            ],
            [
                'titre' => 'Ma première page web', 'slug' => 'html-premiere-page', 'langage' => 'html', 'difficulte' => 'facile', 'matiere_id' => $web, 'gratuit' => true,
                'enonce' => "Complète la page pour qu'elle contienne :\n\n1. un titre `<h1>` avec le texte **Bienvenue**\n2. au moins un paragraphe `<p>`\n3. un lien `<a>` avec un attribut `href`\n\nL'aperçu s'affiche au-dessus quand tu cliques sur « Exécuter ».",
                'depart' => "<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n  <meta charset=\"utf-8\">\n  <title>Ma page</title>\n</head>\n<body>\n  <!-- Écris ton code ici -->\n</body>\n</html>",
                'indice' => "Un lien s'écrit : `<a href=\"https://exemple.com\">texte du lien</a>`.",
                'solution' => "<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n  <meta charset=\"utf-8\">\n  <title>Ma page</title>\n</head>\n<body>\n  <h1>Bienvenue</h1>\n  <p>Ceci est ma première page.</p>\n  <a href=\"https://exemple.com\">Un lien</a>\n</body>\n</html>",
                'tests' => [
                    ['type' => 'html', 'selecteur' => 'h1', 'texte' => 'Bienvenue', 'min' => 1, 'visible' => true],
                    ['type' => 'html', 'selecteur' => 'p', 'texte' => null, 'min' => 1, 'visible' => true],
                    ['type' => 'html', 'selecteur' => 'a[href]', 'texte' => null, 'min' => 1, 'visible' => true],
                ],
            ],
        ];

        foreach ($exercices as $ordre => $e) {
            Exercice::firstOrCreate(
                ['slug' => $e['slug']],
                [
                    'filiere_id' => $filiere->id,
                    'matiere_id' => $e['matiere_id'],
                    'titre' => $e['titre'],
                    'enonce' => $e['enonce'],
                    'langage' => $e['langage'],
                    'code_depart' => $e['depart'],
                    'tests' => $e['tests'],
                    'indice' => $e['indice'],
                    'solution' => $e['solution'],
                    'difficulte' => $e['difficulte'],
                    'ordre' => $ordre + 1,
                    'est_gratuit' => $e['gratuit'],
                    'publie' => true,
                ]
            );
        }
    }
}