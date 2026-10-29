<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeposerAnnaleRequest;
use App\Models\Annale;
use App\Models\Filiere;
use App\Models\Matiere;
use App\Models\Universite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnnaleController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $matiere = $request->filled('matiere')
            ? Matiere::with('filiere')->where('slug', $request->query('matiere'))->where('publie', true)->firstOrFail()
            : null;

        $filiere = $matiere?->filiere
            ?? ($request->filled('filiere')
                ? Filiere::where('slug', $request->query('filiere'))->where('actif', true)->first()
                : null);

        $niveau = in_array($request->query('niveau'), ['L1', 'L2', 'L3'], true) ? $request->query('niveau') : null;

        $annales = Annale::query()
            ->valide()
            ->with(['matiere.filiere', 'universite'])
            ->whereHas('matiere', function ($q) use ($matiere, $filiere, $niveau) {
                $q->where('publie', true)
                  ->when($matiere, fn ($q) => $q->where('id', $matiere->id))
                  ->when(! $matiere && $filiere, fn ($q) => $q->where('filiere_id', $filiere->id))
                  ->when($niveau, fn ($q) => $q->where('niveau', $niveau));
            })
            ->when($request->filled('universite'), fn ($q) => $q->where('universite_id', $request->integer('universite')))
            ->when($request->filled('annee'), fn ($q) => $q->where('annee', $request->integer('annee')))
            ->orderByDesc('annee')
            ->orderBy('matiere_id')
            ->paginate(20)
            ->withQueryString();

        $abonnesFilieres = $user
            ? $user->abonnements()->actif()->with('plan')->get()->pluck('plan.filiere_id')->unique()->values()->all()
            : [];

        return view('annales.index', [
            'annales' => $annales,
            'matiere' => $matiere,
            'filiere' => $filiere,
            'niveau' => $niveau,
            'filieres' => Filiere::where('actif', true)->orderBy('ordre')->get(),
            'universites' => Universite::where('actif', true)->orderBy('nom')->get(),
            'annees' => Annale::valide()->distinct()->orderByDesc('annee')->pluck('annee'),
            'abonnesFilieres' => $abonnesFilieres,
            'user' => $user,
            'theme' => $filiere?->theme,
        ]);
    }

    public function fichier(Request $request, Annale $annale, string $type): StreamedResponse|RedirectResponse
    {
        $annale->load('matiere.filiere', 'universite');

        if (! $annale->peutEtreConsulteePar($request->user())) {
            return redirect()
                ->route('abonnement.index', ['filiere' => $annale->matiere->filiere->slug])
                ->with('erreur', 'Les annales sont réservées aux abonnés de cette filière.');
        }

        $chemin = $annale->cheminFichier($type);

        abort_unless($chemin && Storage::disk('local')->exists($chemin), 404);

        $stream = Storage::disk('local')->readStream($chemin);
        abort_unless(is_resource($stream), 404);

        $disposition = $request->boolean('telecharger') ? 'attachment' : 'inline';
        $nomFichier = $annale->nomTelechargement($type);

        return response()->stream(
            function () use ($stream): void {
                fpassthru($stream);
                fclose($stream);
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'X-Content-Type-Options' => 'nosniff',
                'Content-Disposition' => HeaderUtils::makeDisposition($disposition, $nomFichier),
            ]
        );
    }

    public function deposer(Request $request): View
    {
        $user = $request->user();

        $matieres = Matiere::with('filiere')
            ->where('publie', true)
            ->orderBy('niveau')->orderBy('semestre')->orderBy('ordre')
            ->get()
            ->sortBy(fn (Matiere $m) => ($m->filiere_id === $user->filiere_id ? '0' : '1').$m->filiere->ordre)
            ->groupBy(fn (Matiere $m) => $m->filiere->nom);

        return view('annales.deposer', [
            'matieres' => $matieres,
            'universites' => Universite::where('actif', true)->orderBy('nom')->get(),
            'user' => $user,
            'matiereChoisie' => $request->filled('matiere')
                ? Matiere::where('slug', $request->query('matiere'))->value('id')
                : null,
        ]);
    }

    public function stocker(DeposerAnnaleRequest $request): RedirectResponse
    {
        $user = $request->user();

        $enAttente = Annale::where('user_id', $user->id)->where('statut', 'en_attente')->count();

        if ($enAttente >= 10) {
            return back()->withInput()->with('erreur', 'Tu as déjà 10 dépôts en attente de validation. Patiente le temps qu\'ils soient traités.');
        }

        $donnees = $request->safe()->except(['fichier_sujet', 'fichier_corrige']);
        $donnees['user_id'] = $user->id;
        $donnees['statut'] = 'en_attente';
        $donnees['fichier_sujet'] = $request->file('fichier_sujet')->store('annales', 'local');

        if ($request->hasFile('fichier_corrige')) {
            $donnees['fichier_corrige'] = $request->file('fichier_corrige')->store('annales', 'local');
        }

        Annale::create($donnees);

        return redirect()
            ->route('annales.mes-depots')
            ->with('succes', 'Merci ! Ton annale sera visible après validation par l\'équipe.');
    }

    public function mesDepots(Request $request): View
    {
        return view('annales.mes-depots', [
            'depots' => Annale::where('user_id', $request->user()->id)
                ->with(['matiere.filiere', 'universite'])
                ->latest()
                ->get(),
        ]);
    }
}