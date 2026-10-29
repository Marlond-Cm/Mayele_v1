<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DirectRequest;
use App\Models\ClasseVirtuelle;
use App\Models\Filiere;
use App\Models\Matiere;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DirectController extends Controller
{
    public function index(Request $request): View
    {
        $classes = ClasseVirtuelle::query()
            ->with(['filiere', 'matiere', 'animateur'])
            ->withCount('presences')
            ->when($request->query('quand') === 'passes', fn ($q) => $q->terminees()->orderByDesc('debut_le'))
            ->when($request->query('quand') !== 'passes', fn ($q) => $q->aVenir()->orderBy('debut_le'))
            ->paginate(30)
            ->withQueryString();

        return view('admin.direct.index', ['classes' => $classes]);
    }

    public function create(Request $request): View
    {
        return view('admin.direct.form', $this->donneesFormulaire(new ClasseVirtuelle([
            'debut_le' => now()->addDay()->setTime(18, 0),
            'duree_minutes' => 60,
            'est_gratuit' => false,
            'statut' => 'planifie',
            'animateur_id' => $request->user()->id,
        ])));
    }

    public function store(DirectRequest $request): RedirectResponse
    {
        $donnees = $request->validated();
        $donnees['animateur_id'] = $donnees['animateur_id'] ?? $request->user()->id;
        $donnees['statut'] = $donnees['statut'] ?? 'planifie';
        $donnees['salle'] = $this->nouvelleSalle();

        ClasseVirtuelle::create($donnees);

        return redirect()->route('admin.direct.index')->with('succes', 'Cours en direct planifié.');
    }

    public function edit(ClasseVirtuelle $classe): View
    {
        return view('admin.direct.form', $this->donneesFormulaire($classe));
    }

    public function update(DirectRequest $request, ClasseVirtuelle $classe): RedirectResponse
    {
        $donnees = $request->validated();
        $donnees['animateur_id'] = $donnees['animateur_id'] ?? $classe->animateur_id;
        $donnees['statut'] = $donnees['statut'] ?? $classe->statut;

        $classe->update($donnees);

        return redirect()->route('admin.direct.index')->with('succes', 'Cours en direct mis à jour.');
    }

    public function destroy(ClasseVirtuelle $classe): RedirectResponse
    {
        $classe->delete();

        return redirect()->route('admin.direct.index')->with('succes', 'Cours en direct supprimé.');
    }

    public function presences(ClasseVirtuelle $classe): View
    {
        $classe->load(['filiere', 'matiere']);

        $presences = $classe->presences()->with('user')->orderBy('premiere_entree_at')->get();

        return view('admin.direct.presences', [
            'classe' => $classe,
            'presences' => $presences,
            'moyenne' => $presences->isEmpty() ? 0 : (int) round($presences->avg(fn ($p) => $p->minutes())),
        ]);
    }

    private function donneesFormulaire(ClasseVirtuelle $classe): array
    {
        return [
            'classe' => $classe,
            'filieres' => Filiere::orderBy('ordre')->get(),
            'matieres' => Matiere::orderBy('filiere_id')->orderBy('niveau')->orderBy('semestre')->orderBy('ordre')->get(),
            'animateurs' => User::whereIn('role', ['admin', 'tuteur'])->orderBy('name')->get(),
        ];
    }

    private function nouvelleSalle(): string
    {
        do {
            $salle = 'mayele-'.Str::lower(Str::random(20));
        } while (ClasseVirtuelle::where('salle', $salle)->exists());

        return $salle;
    }
}