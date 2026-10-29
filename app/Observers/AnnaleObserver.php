<?php

namespace App\Observers;

use App\Models\Annale;
use App\Services\RecompenseService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Throwable;

class AnnaleObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Annale $annale): void
    {
        $this->recompenser($annale);
    }

    public function updated(Annale $annale): void
    {
        if ($annale->wasChanged('statut')) {
            $this->recompenser($annale);
        }
    }

    private function recompenser(Annale $annale): void
    {
        if ($annale->statut !== 'valide') {
            return;
        }

        try {
            app(RecompenseService::class)->pourAnnaleValidee($annale);
        } catch (Throwable $e) {
            report($e);
        }
    }
}