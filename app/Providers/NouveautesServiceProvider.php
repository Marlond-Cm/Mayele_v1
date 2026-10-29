<?php

namespace App\Providers;

use App\Models\Annale;
use App\Models\Chapitre;
use App\Models\Matiere;
use App\Models\Nouveaute;
use Illuminate\Support\ServiceProvider;
use Throwable;

class NouveautesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Matiere::saved(function (Matiere $matiere) {
            if ($matiere->publie && ($matiere->wasRecentlyCreated || $matiere->wasChanged('publie'))) {
                $this->annoncer($matiere->filiere_id, 'matiere',
                    'Nouvelle matière : '.$matiere->nom.' ('.$matiere->niveau.')',
                    fn () => route('matieres.show', $matiere->slug));
            }
        });

        Chapitre::saved(function (Chapitre $chapitre) {
            if (! $chapitre->publie || ! ($chapitre->wasRecentlyCreated || $chapitre->wasChanged('publie'))) {
                return;
            }

            $chapitre->loadMissing('matiere');

            // Un chapitre d'une matière encore masquée est annoncé quand la matière est publiée
            if ($chapitre->matiere?->publie) {
                $this->annoncer($chapitre->matiere->filiere_id, 'chapitre',
                    'Nouveau chapitre : '.$chapitre->titre.' ('.$chapitre->matiere->nom.')',
                    fn () => route('chapitres.show', [$chapitre->matiere->slug, $chapitre->slug]));
            }
        });

        Annale::saved(function (Annale $annale) {
            if ($annale->statut === 'valide' && ($annale->wasRecentlyCreated || $annale->wasChanged('statut'))) {
                $annale->loadMissing('matiere', 'universite');

                $this->annoncer($annale->matiere->filiere_id, 'annale',
                    'Nouvelle annale : '.$annale->matiere->nom.' · '.($annale->universite->sigle ?: $annale->universite->nom).' '.$annale->annee,
                    fn () => route('annales.index', ['matiere' => $annale->matiere->slug]));
            }
        });

        if (class_exists(\App\Models\Quiz::class)) {
            \App\Models\Quiz::saved(function ($quiz) {
                if ($quiz->publie && ($quiz->wasRecentlyCreated || $quiz->wasChanged('publie'))) {
                    $quiz->loadMissing('matiere');

                    $this->annoncer($quiz->matiere->filiere_id, 'quiz',
                        'Nouveau quiz : '.$quiz->titre,
                        fn () => route('quiz.show', $quiz));
                }
            });
        }

        if (class_exists(\App\Models\ClasseVirtuelle::class)) {
            \App\Models\ClasseVirtuelle::saved(function ($classe) {
                if ($classe->statut === 'planifie' && $classe->wasRecentlyCreated) {
                    $this->annoncer($classe->filiere_id, 'direct',
                        'Cours en direct : '.$classe->titre.' le '.$classe->debut_le->translatedFormat('d F à H\hi'),
                        fn () => route('direct.show', $classe));
                }
            });
        }

        if (class_exists(\App\Models\Exercice::class)) {
            \App\Models\Exercice::saved(function ($exercice) {
                if ($exercice->publie && ($exercice->wasRecentlyCreated || $exercice->wasChanged('publie'))) {
                    $this->annoncer($exercice->filiere_id, 'exercice',
                        'Nouvel exercice de code : '.$exercice->titre,
                        fn () => route('atelier.show', $exercice->slug));
                }
            });
        }
    }

    /** Une annonce ratée ne doit jamais bloquer l'enregistrement d'un contenu. */
    private function annoncer(?int $filiereId, string $type, string $titre, callable $lien): void
    {
        try {
            Nouveaute::annoncer($filiereId, $type, $titre, $lien());
        } catch (Throwable $e) {
            report($e);
        }
    }
}