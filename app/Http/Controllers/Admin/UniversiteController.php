<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UniversiteRequest;
use App\Models\Universite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UniversiteController extends Controller
{
    public function index(Request $request): View
    {
        $universites = Universite::query()
            ->withCount('etudiants')
            ->when($request->filled('ville'), fn ($q) => $q->where('ville', $request->query('ville')))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $w->where('nom', 'like', '%'.$request->query('q').'%')
                  ->orWhere('sigle', 'like', '%'.$request->query('q').'%');
            }))
            ->orderBy('ville')->orderBy('nom')
            ->paginate(40)
            ->withQueryString();

        return view('admin.universites.index', [
            'universites' => $universites,
            'villes' => Universite::whereNotNull('ville')->distinct()->orderBy('ville')->pluck('ville'),
        ]);
    }

    public function create(): View
    {
        return view('admin.universites.form', [
            'universite' => new Universite(['type' => 'prive', 'actif' => true, 'ville' => 'Pointe-Noire']),
        ]);
    }

    public function store(UniversiteRequest $request): RedirectResponse
    {
        $donnees = $request->validated();
        $donnees['slug'] = $this->slugUnique($donnees['slug'] ?? null, $donnees['nom'], null);

        Universite::create($donnees);

        return redirect()->route('admin.universites.index')->with('succes', 'Université ajoutée.');
    }

    public function edit(Universite $universite): View
    {
        return view('admin.universites.form', ['universite' => $universite]);
    }

    public function update(UniversiteRequest $request, Universite $universite): RedirectResponse
    {
        $donnees = $request->validated();
        $donnees['slug'] = $donnees['slug'] ?: $universite->slug;

        $universite->update($donnees);

        return redirect()->route('admin.universites.index')->with('succes', 'Université mise à jour.');
    }

    public function destroy(Universite $universite): RedirectResponse
    {
        if ($universite->etudiants()->exists() || $universite->annales()->exists()) {
            return back()->with('erreur', 'Impossible de supprimer : des étudiants ou des annales y sont rattachés. Désactive-la plutôt.');
        }

        $universite->delete();

        return redirect()->route('admin.universites.index')->with('succes', 'Université supprimée.');
    }

    private function slugUnique(?string $slug, string $nom, ?int $ignorerId): string
    {
        $base = $slug ?: Str::slug($nom);
        $candidat = $base;
        $i = 2;

        while (Universite::where('slug', $candidat)
            ->when($ignorerId, fn ($q) => $q->where('id', '!=', $ignorerId))
            ->exists()) {
            $candidat = $base.'-'.$i++;
        }

        return $candidat;
    }
}