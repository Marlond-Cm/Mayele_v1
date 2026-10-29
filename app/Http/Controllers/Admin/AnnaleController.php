<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnnaleRequest;
use App\Models\Annale;
use App\Models\Matiere;
use App\Models\Universite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnnaleController extends Controller
{
    public function index(Request $request): View
    {
        $annales = Annale::query()
            ->with(['matiere.filiere', 'universite', 'contributeur'])
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->query('statut')))
            ->when($request->filled('universite'), fn ($q) => $q->where('universite_id', $request->integer('universite')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.annales.index', [
            'annales' => $annales,
            'universites' => Universite::orderBy('nom')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.annales.form', $this->donneesFormulaire(new Annale([
            'annee' => (int) date('Y'), 'session' => 'normale', 'type' => 'examen', 'statut' => 'valide',
        ])));
    }

    public function store(AnnaleRequest $request): RedirectResponse
    {
        $donnees = $request->safe()->except(['fichier_sujet', 'fichier_corrige']);
        $donnees['statut'] = $donnees['statut'] ?? 'valide';
        $donnees['user_id'] = $request->user()->id;
        $donnees['fichier_sujet'] = $request->file('fichier_sujet')->store('annales', 'local');

        if ($request->hasFile('fichier_corrige')) {
            $donnees['fichier_corrige'] = $request->file('fichier_corrige')->store('annales', 'local');
        }

        Annale::create($donnees);

        return redirect()->route('admin.annales.index')->with('succes', 'Annale ajoutée.');
    }

    public function edit(Annale $annale): View
    {
        return view('admin.annales.form', $this->donneesFormulaire($annale));
    }

    public function update(AnnaleRequest $request, Annale $annale): RedirectResponse
    {
        $donnees = $request->safe()->except(['fichier_sujet', 'fichier_corrige']);
        $donnees['statut'] = $donnees['statut'] ?? $annale->statut;

        if ($request->hasFile('fichier_sujet')) {
            Storage::disk('local')->delete($annale->fichier_sujet);
            $donnees['fichier_sujet'] = $request->file('fichier_sujet')->store('annales', 'local');
        }

        if ($request->hasFile('fichier_corrige')) {
            if ($annale->fichier_corrige) {
                Storage::disk('local')->delete($annale->fichier_corrige);
            }
            $donnees['fichier_corrige'] = $request->file('fichier_corrige')->store('annales', 'local');
        }

        $annale->update($donnees);

        return redirect()->route('admin.annales.index')->with('succes', 'Annale mise à jour.');
    }

    public function destroy(Annale $annale): RedirectResponse
    {
        Storage::disk('local')->delete(array_filter([$annale->fichier_sujet, $annale->fichier_corrige]));
        $annale->delete();

        return redirect()->route('admin.annales.index')->with('succes', 'Annale supprimée.');
    }

    public function valider(Annale $annale): RedirectResponse
    {
        $annale->update(['statut' => 'valide']);

        return back()->with('succes', 'Annale validée.');
    }

    public function refuser(Annale $annale): RedirectResponse
    {
        $annale->update(['statut' => 'refuse']);

        return back()->with('succes', 'Annale refusée.');
    }

    public function fichier(Annale $annale, string $type): StreamedResponse
    {
        $chemin = $type === 'sujet' ? $annale->fichier_sujet : $annale->fichier_corrige;

        abort_unless($chemin && Storage::disk('local')->exists($chemin), 404);

        return response()->streamDownload(function () use ($chemin): void {
            $stream = Storage::disk('local')->readStream($chemin);

            if ($stream !== false) {
                fpassthru($stream);
                fclose($stream);
            }
        }, basename($chemin));
    }

    private function donneesFormulaire(Annale $annale): array
    {
        return [
            'annale' => $annale,
            'matieres' => Matiere::with('filiere')
                ->orderBy('filiere_id')->orderBy('niveau')->orderBy('semestre')->orderBy('ordre')
                ->get()
                ->groupBy(fn (Matiere $m) => $m->filiere->nom),
            'universites' => Universite::where('actif', true)->orderBy('nom')->get(),
        ];
    }
}