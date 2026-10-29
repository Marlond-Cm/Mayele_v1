<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExerciceRequest;
use App\Models\Chapitre;
use App\Models\Exercice;
use App\Models\Filiere;
use App\Models\Matiere;
use App\Services\AtelierTestsParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ExerciceController extends Controller
{
    public function index(Request $request): View
    {
        $exercices = Exercice::query()
            ->with(['filiere', 'matiere'])
            ->withCount('progressions')
            ->when(isset(Exercice::LANGAGES[$request->query('langage')]), fn ($q) => $q->where('langage', $request->query('langage')))
            ->when($request->filled('q'), fn ($q) => $q->where('titre', 'like', '%'.$request->query('q').'%'))
            ->orderBy('filiere_id')->orderBy('ordre')->orderBy('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.exercices.index', ['exercices' => $exercices]);
    }

    public function create(): View
    {
        return view('admin.exercices.form', $this->donnees(new Exercice([
            'langage' => 'python', 'difficulte' => 'facile', 'ordre' => 0, 'est_gratuit' => false, 'publie' => false,
        ]), ''));
    }

    public function store(ExerciceRequest $request): RedirectResponse
    {
        $donnees = $request->safe()->except('tests_texte');
        $donnees['tests'] = $request->testsParses();
        $donnees['ordre'] = $donnees['ordre'] ?? 0;
        $donnees['slug'] = $this->slugUnique($donnees['slug'] ?? null, $donnees['titre'], null);

        Exercice::create($donnees);

        return redirect()->route('admin.exercices.index')->with('succes', 'Exercice créé.');
    }

    public function edit(Exercice $exercice): View
    {
        return view('admin.exercices.form', $this->donnees(
            $exercice,
            AtelierTestsParser::serialiser($exercice->langage, $exercice->tests ?? [])
        ));
    }

    public function update(ExerciceRequest $request, Exercice $exercice): RedirectResponse
    {
        $donnees = $request->safe()->except('tests_texte');
        $donnees['tests'] = $request->testsParses();
        $donnees['ordre'] = $donnees['ordre'] ?? $exercice->ordre;
        $donnees['slug'] = ($donnees['slug'] ?? null) ?: $exercice->slug;

        $exercice->update($donnees);

        return redirect()->route('admin.exercices.index')->with('succes', 'Exercice mis à jour.');
    }

    public function destroy(Exercice $exercice): RedirectResponse
    {
        $exercice->delete();

        return redirect()->route('admin.exercices.index')->with('succes', 'Exercice supprimé.');
    }

    private function donnees(Exercice $exercice, string $testsTexte): array
    {
        return [
            'exercice' => $exercice,
            'testsTexte' => $testsTexte,
            'filieres' => Filiere::orderBy('ordre')->get(),
            'matieres' => Matiere::orderBy('filiere_id')->orderBy('niveau')->orderBy('semestre')->orderBy('ordre')->get(),
            'chapitres' => Chapitre::with('matiere')->orderBy('matiere_id')->orderBy('ordre')->get()->groupBy(fn ($c) => $c->matiere->nom),
        ];
    }

    private function slugUnique(?string $slug, string $titre, ?int $ignorerId): string
    {
        $base = $slug ?: Str::slug($titre);
        $candidat = $base;
        $i = 2;

        while (Exercice::where('slug', $candidat)->when($ignorerId, fn ($q) => $q->where('id', '!=', $ignorerId))->exists()) {
            $candidat = $base.'-'.$i++;
        }

        return $candidat;
    }
}