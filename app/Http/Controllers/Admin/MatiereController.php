<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MatiereRequest;
use App\Models\Filiere;
use App\Models\Matiere;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MatiereController extends Controller
{
    public function index(Request $request): View
    {
        $matieres = Matiere::query()
            ->with('filiere')
            ->withCount('chapitres')
            ->when($request->filled('filiere'), fn ($q) => $q->where('filiere_id', $request->integer('filiere')))
            ->when($request->filled('niveau'), fn ($q) => $q->where('niveau', $request->query('niveau')))
            ->when($request->filled('q'), fn ($q) => $q->where('nom', 'like', '%'.$request->query('q').'%'))
            ->orderBy('filiere_id')->orderBy('niveau')->orderBy('semestre')->orderBy('ordre')
            ->paginate(30)
            ->withQueryString();

        return view('admin.matieres.index', [
            'matieres' => $matieres,
            'filieres' => Filiere::orderBy('ordre')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.matieres.form', [
            'matiere' => new Matiere(['niveau' => 'L1', 'semestre' => 1, 'ordre' => 0, 'publie' => false]),
            'filieres' => Filiere::orderBy('ordre')->get(),
        ]);
    }

    public function store(MatiereRequest $request): RedirectResponse
    {
        $donnees = $request->validated();
        $donnees['ordre'] = $donnees['ordre'] ?? 0;
        $donnees['slug'] = $this->slugUnique($donnees, null);

        $matiere = Matiere::create($donnees);

        return redirect()
            ->route('admin.matieres.chapitres.index', $matiere)
            ->with('succes', 'Matière créée. Ajoute maintenant ses chapitres.');
    }

    public function edit(Matiere $matiere): View
    {
        return view('admin.matieres.form', [
            'matiere' => $matiere,
            'filieres' => Filiere::orderBy('ordre')->get(),
        ]);
    }

    public function update(MatiereRequest $request, Matiere $matiere): RedirectResponse
    {
        $donnees = $request->validated();
        $donnees['ordre'] = $donnees['ordre'] ?? $matiere->ordre;
        $donnees['slug'] = $donnees['slug'] ?: $matiere->slug;

        $matiere->update($donnees);

        return redirect()->route('admin.matieres.index')->with('succes', 'Matière mise à jour.');
    }

    public function destroy(Matiere $matiere): RedirectResponse
    {
        $matiere->load(['chapitres', 'annales']);

        foreach ($matiere->chapitres as $chapitre) {
            if ($chapitre->fichier_pdf) {
                Storage::disk('local')->delete($chapitre->fichier_pdf);
            }
        }

        foreach ($matiere->annales as $annale) {
            Storage::disk('local')->delete(array_filter([$annale->fichier_sujet, $annale->fichier_corrige]));
        }

        $matiere->delete();

        return redirect()->route('admin.matieres.index')->with('succes', 'Matière supprimée avec ses chapitres.');
    }

    private function slugUnique(array $donnees, ?int $ignorerId): string
    {
        $base = $donnees['slug'] ?? null;

        if (empty($base)) {
            $filiere = Filiere::findOrFail($donnees['filiere_id']);
            $base = Str::slug(implode(' ', array_filter([
                $filiere->slug, $donnees['nom'], $donnees['niveau'], $donnees['parcours'] ?? null,
            ])));
        }

        $slug = $base;
        $i = 2;

        while (Matiere::where('slug', $slug)
            ->when($ignorerId, fn ($q) => $q->where('id', '!=', $ignorerId))
            ->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}