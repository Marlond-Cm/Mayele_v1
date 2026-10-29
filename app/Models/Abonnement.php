<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Abonnement extends Model
{
    protected $fillable = ['user_id', 'plan_id', 'debut_le', 'fin_le', 'statut', 'montant'];

    protected function casts(): array
    {
        return [
            'debut_le' => 'datetime',
            'fin_le' => 'datetime',
            'montant' => 'integer',
        ];
    }

    public function scopeActif(Builder $query): Builder
    {
        return $query->where('statut', 'actif')->where('fin_le', '>', now());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    public function estActif(): bool
    {
        return $this->statut === 'actif' && $this->fin_le?->isFuture() === true;
    }

    /**
     * Active l'abonnement (après un paiement, un cadeau ou une récompense).
     * La durée s'ajoute à la suite d'un abonnement déjà actif sur la même filière.
     * $jours permet d'imposer une durée (récompenses) au lieu de celle de la formule.
     */
    public function activer(?int $jours = null): void
    {
        $this->loadMissing('plan');

        $debut = now();

        $existant = static::query()
            ->where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->whereHas('plan', fn (Builder $q) => $q->where('filiere_id', $this->plan->filiere_id))
            ->actif()
            ->orderByDesc('fin_le')
            ->first();

        if ($existant !== null) {
            $debut = $existant->fin_le;
        }

        $this->update([
            'statut' => 'actif',
            'debut_le' => $debut,
            'fin_le' => $debut->copy()->addDays($jours ?? $this->plan->duree_jours),
        ]);
    }
}