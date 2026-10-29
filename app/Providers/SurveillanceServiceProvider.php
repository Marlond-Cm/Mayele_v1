<?php

namespace App\Providers;

use App\Models\Paiement;
use App\Models\PaiementJournal;
use GeniusPay\Laravel\Events\PaymentCancelled;
use GeniusPay\Laravel\Events\PaymentCompleted;
use GeniusPay\Laravel\Events\PaymentFailed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class SurveillanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paiement::created(function (Paiement $paiement) {
            PaiementJournal::noter($paiement, 'cree', "Paiement {$paiement->reference} créé : {$paiement->montant} {$paiement->devise}");
        });

        Paiement::updated(function (Paiement $paiement) {
            if ($paiement->wasChanged('statut')) {
                PaiementJournal::noter($paiement, 'statut', "Paiement {$paiement->reference} : {$paiement->getOriginal('statut')} → {$paiement->statut}");
            }
        });

        // Chaque webhook reçu de GeniusPay est noté, avec l'heure du dernier
        Event::listen(
            [PaymentCompleted::class, PaymentFailed::class, PaymentCancelled::class],
            function (object $evenement) {
                Cache::forever('surveillance:dernier_webhook', now()->timestamp);

                $reference = method_exists($evenement, 'getReference') ? $evenement->getReference() : null;

                PaiementJournal::noter(
                    null,
                    'webhook',
                    'Webhook reçu : '.class_basename($evenement).($reference ? " ({$reference})" : ''),
                    ['reference' => $reference]
                );
            }
        );
    }
}