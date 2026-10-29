<?php

namespace App\Services;

use App\Models\Abonnement;
use App\Models\Paiement;
use App\Models\Plan;
use App\Models\User;
use GeniusPay\Laravel\Facades\GeniusPay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PaiementService
{
    /**
     * Crée l'abonnement (en attente), le paiement local, puis le paiement GeniusPay.
     *
     * @return array{paiement: Paiement, url: string}
     */
    public function initier(User $user, Plan $plan, string $telephone): array
    {
        $plan->loadMissing('filiere');

        $paiement = DB::transaction(function () use ($user, $plan, $telephone) {
            $abonnement = Abonnement::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'statut' => 'en_attente',
                'montant' => $plan->prix,
            ]);

            return Paiement::create([
                'user_id' => $user->id,
                'abonnement_id' => $abonnement->id,
                'reference' => $this->nouvelleReference(),
                'montant' => $plan->prix,
                'devise' => config('mayele.devise'),
                'telephone' => $telephone,
                'statut' => 'en_attente',
            ]);
        });

        try {
            $retour = route('paiements.retour', $paiement->reference);

            $distant = GeniusPay::createPayment([
                'amount' => $plan->prix,
                'currency' => config('mayele.devise'),
                'description' => "Abonnement {$plan->nom} - {$plan->filiere->nom}",
                'customer' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $this->telephoneInternational($telephone),
                ],
                'success_url' => $retour,
                'error_url' => $retour,
                'metadata' => [
                    'paiement_id' => $paiement->id,
                    'user_id' => $user->id,
                ],
            ]);

            $url = $distant->paymentUrl ?? null;

            if (empty($url)) {
                throw new RuntimeException('GeniusPay n\'a pas renvoyé d\'URL de paiement.');
            }
        } catch (Throwable $e) {
            $this->echouer($paiement);

            throw $e;
        }

        $paiement->update(['reference_fournisseur' => $distant->reference ?? null]);

        return ['paiement' => $paiement, 'url' => $url];
    }

    /** Demande le statut réel à GeniusPay et l'applique (webhook ou page de retour). */
    public function synchroniser(Paiement $paiement): Paiement
    {
        if ($paiement->statut === 'reussi' || empty($paiement->reference_fournisseur)) {
            return $paiement;
        }

        $distant = GeniusPay::getPayment($paiement->reference_fournisseur);

        if ($distant->isCompleted()) {
            return $this->confirmer($paiement, $distant);
        }

        $statut = $distant->status ?? null;

        if ($distant->isFailed() || in_array($statut, ['cancelled', 'expired'], true)) {
            return $this->echouer($paiement, $distant);
        }

        return $paiement;
    }

    public function confirmer(Paiement $paiement, object $distant): Paiement
    {
        return DB::transaction(function () use ($paiement, $distant) {
            $p = Paiement::whereKey($paiement->id)->lockForUpdate()->firstOrFail();

            if ($p->statut === 'reussi') {
                return $p; // déjà traité : jamais d'activation en double
            }

            if (isset($distant->amount) && (int) round((float) $distant->amount) !== $p->montant) {
                Log::warning('Montant GeniusPay différent du montant attendu.', [
                    'paiement_id' => $p->id,
                    'attendu' => $p->montant,
                    'recu' => $distant->amount,
                ]);
            }

            $p->update([
                'statut' => 'reussi',
                'paye_le' => now(),
                'reponse_brute' => $this->brut($distant),
            ]);

            $p->abonnement()->with('plan')->firstOrFail()->activer();

            return $p->fresh();
        });
    }

    public function echouer(Paiement $paiement, ?object $distant = null): Paiement
    {
        return DB::transaction(function () use ($paiement, $distant) {
            $p = Paiement::whereKey($paiement->id)->lockForUpdate()->firstOrFail();

            if ($p->statut !== 'en_attente') {
                return $p;
            }

            $p->update([
                'statut' => 'echoue',
                'reponse_brute' => $distant ? $this->brut($distant) : $p->reponse_brute,
            ]);

            $p->abonnement()->where('statut', 'en_attente')->update(['statut' => 'annule']);

            return $p;
        });
    }

    public function traiterEvenement(object $event): void
    {
        $reference = method_exists($event, 'getReference') ? $event->getReference() : null;
        $paiementId = method_exists($event, 'getMetadata') ? $event->getMetadata('paiement_id') : null;

        $paiement = $this->retrouver($reference, $paiementId);

        if ($paiement === null) {
            Log::warning('Webhook GeniusPay reçu pour un paiement inconnu.', ['reference' => $reference]);

            return;
        }

        $this->synchroniser($paiement);
    }

    public function retrouver(?string $referenceFournisseur, mixed $paiementId): ?Paiement
    {
        if (! empty($paiementId)) {
            $paiement = Paiement::find($paiementId);

            if ($paiement !== null) {
                return $paiement;
            }
        }

        return $referenceFournisseur
            ? Paiement::where('reference_fournisseur', $referenceFournisseur)->first()
            : null;
    }

    private function nouvelleReference(): string
    {
        do {
            $reference = 'MAY-'.strtoupper(Str::random(10));
        } while (Paiement::where('reference', $reference)->exists());

        return $reference;
    }

    private function telephoneInternational(string $telephone): string
    {
        $telephone = preg_replace('/\s+/', '', $telephone);

        if (str_starts_with($telephone, '+')) {
            return $telephone;
        }

        if (str_starts_with($telephone, '00')) {
            return '+'.substr($telephone, 2);
        }

        if (str_starts_with($telephone, '242')) {
            return '+'.$telephone;
        }

        return '+242'.$telephone;
    }

    private function brut(object $distant): array
    {
        if (method_exists($distant, 'toArray')) {
            return $distant->toArray();
        }

        return json_decode(json_encode($distant), true) ?: [];
    }
}