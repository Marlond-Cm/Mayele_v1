<?php

namespace App\Services;

use App\Models\Quiz;

/**
 * Format de saisie admin (une question = un bloc, séparé par une ligne vide) :
 *
 *   Énoncé de la question ?
 *   = Bonne réponse
 *   - Mauvaise réponse
 *   - Mauvaise réponse
 *   > Explication (facultative)
 */
class QuizParser
{
    private const MAX_QUESTIONS = 60;

    /** @return array{questions: array<int, array<string, mixed>>, erreurs: array<int, string>} */
    public static function parse(string $texte): array
    {
        $texte = trim(str_replace(["\r\n", "\r"], "\n", $texte));
        $blocs = $texte === '' ? [] : preg_split('/\n\s*\n/', $texte);

        $questions = [];
        $erreurs = [];

        if (count($blocs) > self::MAX_QUESTIONS) {
            $erreurs[] = 'Un quiz ne peut pas dépasser '.self::MAX_QUESTIONS.' questions.';
        }

        foreach ($blocs as $i => $bloc) {
            $numero = $i + 1;
            $enonce = [];
            $choix = [];
            $explication = [];

            foreach (explode("\n", $bloc) as $ligne) {
                $ligne = trim($ligne);

                if ($ligne === '') {
                    continue;
                }

                $marque = $ligne[0];

                if ($marque === '=' || $marque === '-') {
                    $choix[] = ['libelle' => trim(substr($ligne, 1)), 'est_correct' => $marque === '='];
                } elseif ($marque === '>') {
                    $explication[] = trim(substr($ligne, 1));
                } elseif ($choix === []) {
                    $enonce[] = $ligne;
                } else {
                    $erreurs[] = "Question {$numero} : la ligne « {$ligne} » doit commencer par = ou - (réponse) ou > (explication).";
                }
            }

            $bonnes = count(array_filter($choix, fn ($c) => $c['est_correct']));

            if ($enonce === []) {
                $erreurs[] = "Question {$numero} : l'énoncé est manquant.";
            }
            if (count($choix) < 2 || count($choix) > 8) {
                $erreurs[] = "Question {$numero} : il faut entre 2 et 8 réponses.";
            }
            if ($bonnes !== 1) {
                $erreurs[] = "Question {$numero} : il faut exactement une bonne réponse (ligne commençant par =).";
            }
            if (array_filter($choix, fn ($c) => $c['libelle'] === '') !== []) {
                $erreurs[] = "Question {$numero} : une réponse est vide.";
            }

            $questions[] = [
                'enonce' => implode(' ', $enonce),
                'explication' => $explication === [] ? null : implode(' ', $explication),
                'choix' => $choix,
            ];
        }

        if ($questions === []) {
            $erreurs[] = 'Ajoute au moins une question.';
        }

        return ['questions' => $questions, 'erreurs' => $erreurs];
    }

    /** Reconstruit le texte d'édition à partir d'un quiz existant. */
    public static function serialiser(Quiz $quiz): string
    {
        $quiz->load('questions.choix');

        return $quiz->questions->map(function ($question) {
            $lignes = [$question->enonce];

            foreach ($question->choix as $choix) {
                $lignes[] = ($choix->est_correct ? '= ' : '- ').$choix->libelle;
            }

            if ($question->explication) {
                $lignes[] = '> '.$question->explication;
            }

            return implode("\n", $lignes);
        })->implode("\n\n");
    }
}