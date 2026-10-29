<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Models\PaiementJournal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SurveillanceController extends Controller
{
    public function index(): View
    {
        $dernier = Cache::get('surveillance:dernier_webhook');
        $seuil = (int) config('surveillance.attente_minutes');

        $enAttente = Paiement::query()
            ->with(['user', 'abonnement.plan.filiere'])
            ->where('statut', 'en_attente')
            ->where('created_at', '>=', now()->subDays(2))
            ->orderBy('created_at')
            ->get();

        return view('admin.surveillance.index', [
            'dernierWebhook' => $dernier ? Carbon::createFromTimestamp((int) $dernier) : null,
            'enAttente' => $enAttente,
            'nbBloques' => $enAttente->filter(fn ($p) => $p->created_at->lte(now()->subMinutes($seuil)))->count(),
            'seuil' => $seuil,
            'compteurs' => PaiementJournal::query()
                ->where('created_at', '>=', now()->subDay())
                ->selectRaw('type, COUNT(*) as total')
                ->groupBy('type')
                ->pluck('total', 'type'),
            'journal' => PaiementJournal::with('paiement')->latest()->paginate(40),
        ]);
    }

    public function verifier(): RedirectResponse
    {
        Artisan::call('mayele:surveiller-paiements', ['--sans-alerte' => true]);

        return back()->with('succes', trim(Artisan::output()) ?: 'Vérification terminée.');
    }
}