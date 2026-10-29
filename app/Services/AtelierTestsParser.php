<?php

namespace App\Services;

/**
 * Formats de saisie des tests (blocs séparés par une ligne « --- »).
 *
 * Python / JavaScript :   [visible]            SQL :  [visible]               HTML :  [visible]
 *                         entree:                     preparation:                   selecteur: h1
 *                         3                           CREATE TABLE ...;              texte: Bienvenue
 *                         5                           attendu:                       min: 1
 *                         sortie:                     Aline | 12
 *                         8                           ordre: oui
 *
 * Le premier mot entre crochets est facultatif : [visible] (par défaut) ou [cache].
 */
class AtelierTestsParser
{
    /** @return array{tests: array<int, array<string, mixed>>, erreurs: array<int, string>} */
    public static function parse(string $langage, string $texte): array
    {
        $texte = trim(str_replace(["\r\n", "\r"], "\n", $texte));
        $blocs = $texte === '' ? [] : preg_split('/^[ \t]*---[ \t]*$/m', $texte);

        $tests = [];
        $erreurs = [];

        foreach ($blocs as $i => $bloc) {
            $numero = $i + 1;
            $lignes = explode("\n", trim($bloc, "\n"));
            $visible = true;

            if (isset($lignes[0]) && preg_match('/^\s*\[(visible|cache|caché)\]\s*$/iu', $lignes[0], $m)) {
                $visible = mb_strtolower($m[1]) === 'visible';
                array_shift($lignes);
            }

            $test = match ($langage) {
                'sql' => self::parserSql($lignes, $numero, $erreurs),
                'html' => self::parserHtml($lignes, $numero, $erreurs),
                default => self::parserEntreeSortie($lignes, $numero, $erreurs),
            };

            if ($test !== null) {
                $test['visible'] = $visible;
                $tests[] = $test;
            }
        }

        if ($tests === [] && $erreurs === []) {
            $erreurs[] = 'Ajoute au moins un test.';
        }

        return ['tests' => $tests, 'erreurs' => $erreurs];
    }

    public static function serialiser(string $langage, array $tests): string
    {
        $blocs = [];

        foreach ($tests as $test) {
            $lignes = [($test['visible'] ?? true) ? '[visible]' : '[cache]'];

            if ($langage === 'sql') {
                $lignes[] = 'preparation:';
                $lignes[] = $test['preparation'] ?? '';
                $lignes[] = 'attendu:';

                foreach ($test['attendu'] ?? [] as $ligne) {
                    $lignes[] = implode(' | ', $ligne);
                }

                $lignes[] = 'ordre: '.(($test['ordre'] ?? false) ? 'oui' : 'non');
            } elseif ($langage === 'html') {
                $lignes[] = 'selecteur: '.($test['selecteur'] ?? '');

                if (! empty($test['texte'])) {
                    $lignes[] = 'texte: '.$test['texte'];
                }

                $lignes[] = 'min: '.($test['min'] ?? 1);
            } else {
                $lignes[] = 'entree:';

                if (($test['entree'] ?? '') !== '') {
                    $lignes[] = $test['entree'];
                }

                $lignes[] = 'sortie:';
                $lignes[] = $test['sortie'] ?? '';
            }

            $blocs[] = implode("\n", $lignes);
        }

        return implode("\n---\n", $blocs);
    }

    private static function parserEntreeSortie(array $lignes, int $numero, array &$erreurs): ?array
    {
        $zone = null;
        $entree = [];
        $sortie = [];
        $vuSortie = false;

        foreach ($lignes as $ligne) {
            $propre = trim($ligne);

            if (preg_match('/^entr[ée]e\s*:\s*$/iu', $propre)) {
                $zone = 'entree';

                continue;
            }

            if (preg_match('/^sortie\s*:\s*$/iu', $propre)) {
                $zone = 'sortie';
                $vuSortie = true;

                continue;
            }

            if ($zone === 'entree') {
                $entree[] = rtrim($ligne);
            } elseif ($zone === 'sortie') {
                $sortie[] = rtrim($ligne);
            } elseif ($propre !== '') {
                $erreurs[] = "Test {$numero} : ligne inattendue « {$propre} » (écris « entree: » ou « sortie: » d'abord).";
            }
        }

        $sortieTexte = rtrim(implode("\n", $sortie), "\n");

        if (! $vuSortie || trim($sortieTexte) === '') {
            $erreurs[] = "Test {$numero} : la ligne « sortie: » et le résultat attendu sont obligatoires.";

            return null;
        }

        return ['type' => 'io', 'entree' => rtrim(implode("\n", $entree), "\n"), 'sortie' => $sortieTexte];
    }

    private static function parserSql(array $lignes, int $numero, array &$erreurs): ?array
    {
        $zone = null;
        $preparation = [];
        $attendu = [];
        $ordre = false;

        foreach ($lignes as $ligne) {
            $propre = trim($ligne);

            if (preg_match('/^pr[ée]paration\s*:\s*$/iu', $propre)) {
                $zone = 'preparation';

                continue;
            }

            if (preg_match('/^attendu\s*:\s*$/iu', $propre)) {
                $zone = 'attendu';

                continue;
            }

            if (preg_match('/^ordre\s*:\s*(oui|non)\s*$/iu', $propre, $m)) {
                $ordre = mb_strtolower($m[1]) === 'oui';

                continue;
            }

            if ($zone === 'preparation') {
                $preparation[] = $ligne;
            } elseif ($zone === 'attendu' && $propre !== '') {
                $attendu[] = array_map('trim', explode('|', $ligne));
            } elseif ($propre !== '') {
                $erreurs[] = "Test {$numero} : ligne inattendue « {$propre} » (écris « preparation: » ou « attendu: » d'abord).";
            }
        }

        $sql = trim(implode("\n", $preparation));

        if ($sql === '') {
            $erreurs[] = "Test {$numero} : la partie « preparation: » (création et remplissage des tables) est obligatoire.";

            return null;
        }

        if ($attendu === []) {
            $erreurs[] = "Test {$numero} : la partie « attendu: » (les lignes du résultat, colonnes séparées par |) est obligatoire.";

            return null;
        }

        return ['type' => 'sql', 'preparation' => $sql, 'attendu' => $attendu, 'ordre' => $ordre];
    }

    private static function parserHtml(array $lignes, int $numero, array &$erreurs): ?array
    {
        $selecteur = null;
        $texte = null;
        $min = 1;

        foreach ($lignes as $ligne) {
            $propre = trim($ligne);

            if ($propre === '') {
                continue;
            }

            if (preg_match('/^selecteur\s*:\s*(.+)$/iu', $propre, $m)) {
                $selecteur = trim($m[1]);
            } elseif (preg_match('/^texte\s*:\s*(.+)$/iu', $propre, $m)) {
                $texte = trim($m[1]);
            } elseif (preg_match('/^min\s*:\s*(\d+)$/iu', $propre, $m)) {
                $min = max(1, (int) $m[1]);
            } else {
                $erreurs[] = "Test {$numero} : ligne inattendue « {$propre} » (utilise selecteur:, texte: ou min:).";
            }
        }

        if ($selecteur === null) {
            $erreurs[] = "Test {$numero} : la ligne « selecteur: » est obligatoire (ex. selecteur: h1).";

            return null;
        }

        return ['type' => 'html', 'selecteur' => $selecteur, 'texte' => $texte, 'min' => $min];
    }
}