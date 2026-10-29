<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChapitreRequest;
use App\Models\Chapitre;
use App\Models\Matiere;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChapitreController extends Controller
{
    public function index(Matiere $matiere): View
    {
        $matiere->load('filiere');

        return view('admin.chapitres.index', [
            'matiere' => $matiere,
            'chapitres' => Chapitre::where('matiere_id', $matiere->id)->orderBy('ordre')->get(),
        ]);
    }

    public function create(Matiere $matiere): View
    {
        return view('admin.chapitres.form', [
            'matiere' => $matiere,
            'chapitre' => new Chapitre(['est_gratuit' => false, 'publie' => false]),
        ]);
    }

    public function store(ChapitreRequest $request, Matiere $matiere): RedirectResponse
    {
        $donnees = $request->safe()->except(['fichier_pdf', 'supprimer_pdf']);

        $donnees['matiere_id'] = $matiere->id;
        $donnees['slug'] = $this->slugUnique($matiere->id, $donnees['slug'] ?? null, $donnees['titre'], null);
        $donnees['ordre'] = $donnees['ordre'] ?? ((int) Chapitre::where('matiere_id', $matiere->id)->max('ordre') + 1);
        $donnees['duree_minutes'] = $donnees['duree_minutes'] ?? 0;

        if ($request->hasFile('fichier_pdf')) {
            $donnees['fichier_pdf'] = $request->file('fichier_pdf')->store('chapitres/'.$matiere->id, 'local');
        }

        Chapitre::create($donnees);

        return redirect()->route('admin.matieres.chapitres.index', $matiere)->with('succes', 'Chapitre ajouté.');
    }

    public function edit(Chapitre $chapitre): View
    {
        return view('admin.chapitres.form', [
            'matiere' => $chapitre->matiere,
            'chapitre' => $chapitre,
        ]);
    }

    public function update(ChapitreRequest $request, Chapitre $chapitre): RedirectResponse
    {
        $donnees = $request->safe()->except(['fichier_pdf', 'supprimer_pdf']);

        $donnees['slug'] = ($donnees['slug'] ?? null) ?: $chapitre->slug;
        $donnees['ordre'] = $donnees['ordre'] ?? $chapitre->ordre;
        $donnees['duree_minutes'] = $donnees['duree_minutes'] ?? 0;

        if ($request->hasFile('fichier_pdf')) {
            if ($chapitre->fichier_pdf) {
                Storage::disk('local')->delete($chapitre->fichier_pdf);
            }
            $donnees['fichier_pdf'] = $request->file('fichier_pdf')->store('chapitres/'.$chapitre->matiere_id, 'local');
        } elseif ($request->boolean('supprimer_pdf') && $chapitre->fichier_pdf) {
            Storage::disk('local')->delete($chapitre->fichier_pdf);
            $donnees['fichier_pdf'] = null;
        }

        $chapitre->update($donnees);

        return redirect()
            ->route('admin.matieres.chapitres.index', $chapitre->matiere_id)
            ->with('succes', 'Chapitre mis à jour.');
    }

    public function destroy(Chapitre $chapitre): RedirectResponse
    {
        $matiereId = $chapitre->matiere_id;

        if ($chapitre->fichier_pdf) {
            Storage::disk('local')->delete($chapitre->fichier_pdf);
        }

        $chapitre->delete();

        return redirect()->route('admin.matieres.chapitres.index', $matiereId)->with('succes', 'Chapitre supprimé.');
    }

    private function slugUnique(int $matiereId, ?string $slug, string $titre, ?int $ignorerId): string
    {
        $base = $slug ?: Str::slug($titre);
        $candidat = $base;
        $i = 2;

        while (Chapitre::where('matiere_id', $matiereId)
            ->where('slug', $candidat)
            ->when($ignorerId, fn ($q) => $q->where('id', '!=', $ignorerId))
            ->exists()) {
            $candidat = $base.'-'.$i++;
        }

        return $candidat;
    }
}