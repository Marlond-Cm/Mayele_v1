<?php

namespace App\Observers;

use App\Models\Paiement;
use App\Services\RecompenseService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Throwable;

class PaiementObserver implements ShouldHandleEventsAfterCommit
{
    public function updated(Paiement $paiement): void
    {
        if (! $paiement->wasChanged('statut') || $paiement->statut !== 'reussi') {
            return;
        }

        try {
            app(RecompenseService::class)->pourPaiementReussi($paiement);
        } catch (Throwable $e) {
            report($e); // une récompense ratée ne doit jamais bloquer un paiement
        }
    }
}