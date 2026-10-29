<?php

namespace App\Services;

use App\Models\Abonnement;
use App\Models\Annale;
use App\Models\Paiement;
use App\Models\Recompense;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class RecompenseService
{
    /** Le parrain gagne des jours quand son filleul réussit son tout premier vrai paiement. */
    public function pourPaiementReussi(Paiement $paiement): void
    {
        $filleul = $paiement->user;

        if ($filleul === null || empty($filleul->parrain_id) || $paiement->montant <= 0) {
            return;
        }

        $premierId = Paiement::query()
            ->where('user_id', $filleul->id)
            ->where('statut', 'reussi')
            ->where('montant', '>', 0)
            ->orderBy('paye_le')
            ->orderBy('id')
            ->value('id');

        if ($premierId !== $paiement->id) {
            return;
        }

        $parrain = User::find($filleul->parrain_id);

        if ($parrain !== null) {
            $this->accorder($parrain, 'parrainage', $filleul->id, (int) config('mayele.parrainage_jours'));
        }
    }

    /** L'auteur d'une annale validée gagne des jours (plafonné). Les annales de l'admin n'en donnent pas. */
    public function pourAnnaleValidee(Annale $annale): void
    {
        if (empty($annale->user_id)) {
            return;
        }

        $auteur = User::find($annale->user_id);

        if ($auteur === null || $auteur->estAdmin()) {
            return;
        }

        $dejaRecues = Recompense::where('user_id', $auteur->id)->where('type', 'annale')->count();

        if ($dejaRecues >= (int) config('mayele.annale_max')) {
            return;
        }

        $this->accorder($auteur, 'annale', $annale->id, (int) config('mayele.annale_jours'));
    }

    /**
     * Crée la récompense et active les jours offerts sur la filière de l'étudiant.
     * L'index unique (user, type, source) garantit qu'une récompense n'est jamais versée deux fois.
     */
    private function accorder(User $user, string $type, int $sourceId, int $jours): void
    {
        $plan = $user->filiere?->plans()->where('actif', true)->orderBy('duree_jours')->first();

        if ($plan === null || $jours <= 0) {
            return;
        }

        try {
            DB::transaction(function () use ($user, $type, $sourceId, $jours, $plan) {
                $recompense = Recompense::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'source_id' => $sourceId,
                    'jours' => $jours,
                ]);

                $abonnement = Abonnement::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'statut' => 'en_attente',
                    'montant' => 0,
                ]);

                $abonnement->activer($jours);

                $recompense->update(['abonnement_id' => $abonnement->id]);
            });
        } catch (UniqueConstraintViolationException) {
            // Récompense déjà accordée : rien à faire
        }
    }
}