<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UtilisateurController extends Controller
{
    public function index(Request $request): View
    {
        $utilisateurs = User::query()
            ->with(['filiere', 'universite'])
            ->withCount(['abonnements as abonnements_actifs' => fn ($q) => $q->actif()])
            ->when($request->filled('q'), function ($q) use ($request) {
                $terme = '%'.$request->query('q').'%';

                $q->where(function ($w) use ($terme) {
                    $w->where('name', 'like', $terme)
                      ->orWhere('email', 'like', $terme)
                      ->orWhere('telephone', 'like', $terme);
                });
            })
            ->when($request->query('abonne') === '1', fn ($q) => $q->whereHas('abonnements', fn ($a) => $a->actif()))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.utilisateurs.index', ['utilisateurs' => $utilisateurs]);
    }

    public function show(User $user): View
    {
        $user->load(['filiere', 'universite', 'parrain']);

        return view('admin.utilisateurs.show', [
            'utilisateur' => $user,
            'abonnements' => $user->abonnements()->with('plan.filiere')->latest()->get(),
            'paiements' => $user->paiements()->with('abonnement.plan')->latest()->limit(15)->get(),
            'filleuls' => $user->filleuls()->count(),
            'plans' => Plan::with('filiere')->where('actif', true)->orderBy('filiere_id')->orderBy('duree_jours')->get(),
        ]);
    }

    /** Offre un abonnement (paiement en espèces, geste commercial, test). */
    public function offrir(Request $request, User $user): RedirectResponse
    {
        $donnees = $request->validate([
            'plan_id' => ['required', Rule::exists('plans', 'id')],
        ], [
            'plan_id.required' => 'Choisis une formule.',
        ]);

        $plan = Plan::findOrFail($donnees['plan_id']);

        $abonnement = Abonnement::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'statut' => 'en_attente',
            'montant' => 0,
        ]);

        $abonnement->activer();

        return back()->with('succes', "Abonnement {$plan->nom} activé pour {$user->name}.");
    }
}