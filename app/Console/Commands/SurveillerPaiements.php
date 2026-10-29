<?php

namespace App\Console\Commands;

use App\Mail\AlerteSurveillance;
use App\Models\Paiement;
use App\Models\PaiementJournal;
use App\Models\User;
use App\Services\PaiementService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SurveillerPaiements extends Command
{
    protected $signature = 'mayele:surveiller-paiements {--sans-alerte : Vérifie sans envoyer d\'e-mail}';

    protected $description = 'Rattrape les paiements dont le webhook manque et alerte l\'administration en cas de blocage.';

    public function handle(PaiementService $paiements): int
    {
        $attente = (int) config('surveillance.attente_minutes');
        $blocage = (int) config('surveillance.blocage_minutes');
        $minimum = (int) config('surveillance.blocage_minimum');
        $fenetre = (int) config('surveillance.fenetre_heures');

        $problemes = [];

        // 1. Vérification directe auprès de GeniusPay des paiements en attente depuis un moment
        $aVerifier = Paiement::query()
            ->where('statut', 'en_attente')
            ->whereNotNull('reference_fournisseur')
            ->where('created_at', '<=', now()->subMinutes($attente))
            ->where('created_at', '>=', now()->subDays(2))
            ->get();

        $rattrapes = 0;
        $erreursApi = 0;

        foreach ($aVerifier as $paiement) {
            try {
                $apres = $paiements->synchroniser($paiement);

                if ($apres->statut !== 'en_attente') {
                    $rattrapes++;
                    PaiementJournal::noter($apres, 'reconciliation', "Statut {$apres->statut} récupéré par vérification directe : le webhook n'est pas arrivé en {$attente} minutes.");
                }
            } catch (Throwable $e) {
                $erreursApi++;
                PaiementJournal::noter($paiement, 'erreur_api', 'GeniusPay injoignable : '.$e->getMessage());
            }
        }

        if ($rattrapes > 0) {
            $problemes['webhook'] = "{$rattrapes} paiement(s) n'ont pas été confirmés par webhook et ont été récupérés par vérification directe. Les notifications de GeniusPay semblent retardées ou bloquées : vérifie l'adresse du webhook dans ton tableau de bord GeniusPay.";
        }

        if ($erreursApi > 0) {
            $problemes['api'] = "GeniusPay est injoignable ({$erreursApi} erreur(s) pendant la vérification). Les paiements en attente ne peuvent pas être confirmés.";
        }

        // 2. Non-réponse prolongée : plusieurs paiements bloqués et aucun paiement réussi sur la période
        $recents = Paiement::where('created_at', '>=', now()->subHours($fenetre))->get(['id', 'statut', 'created_at']);
        $nbBloques = $recents
            ->where('statut', 'en_attente')
            ->filter(fn ($p) => $p->created_at->lte(now()->subMinutes($blocage)))
            ->count();

        if ($nbBloques >= $minimum && $recents->where('statut', 'reussi')->isEmpty()) {
            $problemes['operateurs'] = "{$nbBloques} paiements sont en attente depuis plus de {$blocage} minutes et aucun paiement n'a réussi depuis {$fenetre} heures : les réseaux mobiles (Airtel, MTN) ou GeniusPay ne répondent peut-être plus.";
        }

        $this->line("Vérifiés : {$aVerifier->count()} · rattrapés : {$rattrapes} · erreurs GeniusPay : {$erreursApi} · bloqués : {$nbBloques}");

        if ($problemes === []) {
            $this->info('Aucun problème détecté.');

            return self::SUCCESS;
        }

        foreach ($problemes as $message) {
            $this->warn($message);
        }

        if (! $this->option('sans-alerte')) {
            $this->alerter($problemes);
        }

        return self::SUCCESS;
    }

    private function alerter(array $problemes): void
    {
        $pause = (int) config('surveillance.pause_alerte_minutes');
        $nouveaux = [];

        foreach ($problemes as $cle => $message) {
            if (Cache::add("surveillance:alerte:{$cle}", 1, now()->addMinutes($pause))) {
                $nouveaux[$cle] = $message;
            }
        }

        if ($nouveaux === []) {
            return;
        }

        $emails = User::where('role', 'admin')->pluck('email')->filter()->all();

        if ($emails === []) {
            Log::warning('Alerte paiements sans destinataire : aucun administrateur.', $nouveaux);

            return;
        }

        try {
            Mail::to($emails)->send(new AlerteSurveillance($nouveaux));
            PaiementJournal::noter(null, 'alerte', 'Alerte envoyée : '.implode(', ', array_keys($nouveaux)));
        } catch (Throwable $e) {
            report($e);
        }
    }
}