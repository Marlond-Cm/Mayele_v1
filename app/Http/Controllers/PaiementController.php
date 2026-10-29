<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Services\PaiementService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PaiementController extends Controller
{
    public function retour(Request $request, Paiement $paiement, PaiementService $paiements): View
    {
        abort_unless($paiement->user_id === $request->user()->id, 404);

        if ($paiement->statut === 'en_attente') {
            try {
                $paiement = $paiements->synchroniser($paiement);
            } catch (Throwable $e) {
                report($e); // on réessaiera au prochain rafraîchissement
            }
        }

        $paiement->load('abonnement.plan.filiere');

        return view('paiements.retour', [
            'paiement' => $paiement,
            'theme' => $paiement->abonnement->plan->filiere->theme,
        ]);
    }
}