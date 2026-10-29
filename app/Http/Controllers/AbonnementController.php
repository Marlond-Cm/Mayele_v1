<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayerRequest;
use App\Models\Filiere;
use App\Models\Plan;
use App\Services\PaiementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AbonnementController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $filiere = $request->filled('filiere')
            ? Filiere::where('slug', $request->query('filiere'))->where('actif', true)->firstOrFail()
            : $user->filiere;

        abort_if($filiere === null, 404);

        $plans = $filiere->plans()->where('actif', true)->orderBy('duree_jours')->get();

        $abonnement = $user->abonnements()
            ->actif()
            ->whereHas('plan', fn ($q) => $q->where('filiere_id', $filiere->id))
            ->with('plan')
            ->orderByDesc('fin_le')
            ->first();

        $paiements = $user->paiements()
            ->with('abonnement.plan.filiere')
            ->latest()
            ->limit(10)
            ->get();

        return view('abonnement.index', [
            'user' => $user,
            'filiere' => $filiere,
            'plans' => $plans,
            'mensuel' => $plans->firstWhere('duree_jours', 30),
            'planParDefaut' => $plans->firstWhere('duree_jours', 90)?->id ?? $plans->first()?->id,
            'abonnement' => $abonnement,
            'paiements' => $paiements,
            'theme' => $filiere->theme,
        ]);
    }

    public function payer(PayerRequest $request, PaiementService $paiements): RedirectResponse
    {
        $plan = Plan::with('filiere')->findOrFail($request->validated('plan_id'));

        abort_unless($plan->filiere->actif, 404);

        try {
            $resultat = $paiements->initier($request->user(), $plan, $request->validated('telephone'));
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('abonnement.index', ['filiere' => $plan->filiere->slug])
                ->withInput()
                ->with('erreur', 'Le service de paiement est momentanément indisponible. Réessaie dans quelques minutes.');
        }

        return redirect()->away($resultat['url']);
    }
}