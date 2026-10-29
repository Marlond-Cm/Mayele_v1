<?php

namespace App\Http\Controllers;

use App\Models\Chapitre;
use App\Models\Matiere;
use App\Models\Progression;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChapitreController extends Controller
{
    public function show(Request $request, Matiere $matiere, Chapitre $chapitre): View
    {
        abort_unless($matiere->publie && $chapitre->publie, 404);

        $matiere->load('filiere');
        $chapitre->setRelation('matiere', $matiere);

        $user = $request->user();
        $accessible = $chapitre->estAccessiblePour($user);

        $chapitres = $matiere->chapitres()->where('publie', true)->get();
        $index = $chapitres->search(fn (Chapitre $c) => $c->id === $chapitre->id);
        $precedent = $index > 0 ? $chapitres->get($index - 1) : null;
        $suivant = $chapitres->get($index + 1);

        $termine = $user && $accessible
            ? Progression::query()
                ->where('user_id', $user->id)
                ->where('chapitre_id', $chapitre->id)
                ->whereNotNull('termine_le')
                ->exists()
            : false;

        $plans = $accessible
            ? collect()
            : $matiere->filiere->plans()->where('actif', true)->orderBy('duree_jours')->get();

        // Markdown sécurisé : le HTML brut saisi dans le contenu est supprimé
        $contenuHtml = $accessible && $chapitre->contenu
            ? Str::markdown($chapitre->contenu, ['html_input' => 'strip', 'allow_unsafe_links' => false])
            : null;

        return view('chapitres.show', [
            'matiere' => $matiere,
            'chapitre' => $chapitre,
            'accessible' => $accessible,
            'contenuHtml' => $contenuHtml,
            'precedent' => $precedent,
            'suivant' => $suivant,
            'termine' => $termine,
            'plans' => $plans,
            'theme' => $matiere->filiere->theme,
        ]);
    }

    /** Les PDF sont stockés sur le disque privé : accès contrôlé, jamais d'URL publique. */
    public function pdf(Request $request, Chapitre $chapitre): StreamedResponse
    {
        $chapitre->load('matiere');

        abort_unless($chapitre->publie && $chapitre->matiere->publie && $chapitre->fichier_pdf, 404);
        abort_unless($chapitre->estAccessiblePour($request->user()), 403);
        abort_unless(Storage::disk('local')->exists($chapitre->fichier_pdf), 404);

        return response()->stream(function () use ($chapitre): void {
            $stream = Storage::disk('local')->readStream($chapitre->fichier_pdf);

            if ($stream !== false) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($chapitre->fichier_pdf) . '"',
        ]);
    }

    public function terminer(Request $request, Chapitre $chapitre): RedirectResponse
    {
        $chapitre->load('matiere.filiere');
        $matiere = $chapitre->matiere;

        abort_unless($chapitre->publie && $matiere->publie, 404);
        abort_unless($chapitre->estAccessiblePour($request->user()), 403);

        Progression::updateOrCreate(
            ['user_id' => $request->user()->id, 'chapitre_id' => $chapitre->id],
            ['termine_le' => now()]
        );

        $suivant = $matiere->chapitres()
            ->where('publie', true)
            ->where('ordre', '>', $chapitre->ordre)
            ->first();

        return $suivant
            ? redirect()->route('chapitres.show', [$matiere->slug, $suivant->slug])
            : redirect()->route('matieres.show', $matiere->slug);
    }
}