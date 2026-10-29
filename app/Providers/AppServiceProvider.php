<?php

namespace App\Providers;

use App\Models\Annale;
use App\Models\Paiement;
use App\Observers\AnnaleObserver;
use App\Observers\PaiementObserver;
use App\Services\PaiementService;
use GeniusPay\Laravel\Events\PaymentCancelled;
use GeniusPay\Laravel\Events\PaymentCompleted;
use GeniusPay\Laravel\Events\PaymentFailed;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        Paiement::observe(PaiementObserver::class);
        Annale::observe(AnnaleObserver::class);

        // Chaque webhook GeniusPay déclenche une re-vérification auprès de l'API
        Event::listen(
            [PaymentCompleted::class, PaymentFailed::class, PaymentCancelled::class],
            function (object $event) {
                app(PaiementService::class)->traiterEvenement($event);
            }
        );
    }
}