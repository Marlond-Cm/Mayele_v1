<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Annale extends Model
{
    protected $fillable = [
        'matiere_id', 'universite_id', 'user_id', 'annee', 'session',
        'type', 'fichier_sujet', 'fichier_corrige', 'statut',
    ];

    protected function casts(): array
    {
        return ['annee' => 'integer'];
    }

    public function scopeValide(Builder $query): Builder
    {
        return $query->where('statut', 'valide');
    }

    public function matiere(): BelongsTo
    {
        return $this->belongsTo(Matiere::class);
    }

    public function universite(): BelongsTo
    {
        return $this->belongsTo(Universite::class);
    }

    public function contributeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cheminFichier(string $type): ?string
    {
        return $type === 'corrige' ? $this->fichier_corrige : $this->fichier_sujet;
    }

    public function nomTelechargement(string $type): string
    {
        $this->loadMissing('matiere', 'universite');

        return Str::slug(implode(' ', [
            $this->matiere->nom,
            $this->universite->sigle ?: $this->universite->nom,
            $this->annee,
            $this->session,
            $this->type,
            $type,
        ])).'.pdf';
    }

    /**
     * Accès aux fichiers : admin, déposant (son propre dépôt),
     * ou abonné actif de la filière pour une annale validée.
     */
    public function peutEtreConsulteePar(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        if ($user->estAdmin() || $user->id === $this->user_id) {
            return true;
        }

        $this->loadMissing('matiere');

        return $this->statut === 'valide'
            && $user->aAbonnementActifPour($this->matiere->filiere_id);
    }
}