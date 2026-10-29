<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Services\PaiementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PaiementController extends Controller
{
    public function index(Request $request): View
    {
        $paiements = Paiement::query()
            ->with(['user', 'abonnement.plan.filiere'])
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->query('statut')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $terme = '%'.$request->query('q').'%';

                $q->where(function ($w) use ($terme) {
                    $w->where('reference', 'like', $terme)
                      ->orWhereHas('user', fn ($u) => $u->where('email', 'like', $terme)
                          ->orWhere('name', 'like', $terme)
                          ->orWhere('telephone', 'like', $terme));
                });
            })
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.paiements.index', ['paiements' => $paiements]);
    }

    public function verifier(Paiement $paiement, PaiementService $service): RedirectResponse
    {
        try {
            $paiement = $service->synchroniser($paiement);
        } catch (Throwable $e) {
            report($e);

            return back()->with('erreur', 'Impossible de joindre GeniusPay pour le moment.');
        }

        $libelles = ['reussi' => 'réussi', 'echoue' => 'échoué', 'en_attente' => 'toujours en attente'];

        return back()->with('succes', "Paiement {$paiement->reference} : {$libelles[$paiement->statut]}.");
    }
}