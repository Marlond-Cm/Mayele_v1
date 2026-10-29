<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    protected $fillable = [
        'user_id', 'abonnement_id', 'reference', 'reference_fournisseur',
        'montant', 'devise', 'operateur', 'telephone', 'statut',
        'reponse_brute', 'paye_le',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'integer',
            'reponse_brute' => 'array',
            'paye_le' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function abonnement(): BelongsTo
    {
        return $this->belongsTo(Abonnement::class);
    }
}