<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = ['filiere_id', 'nom', 'duree_jours', 'prix', 'actif'];

    protected function casts(): array
    {
        return [
            'duree_jours' => 'integer',
            'prix' => 'integer',
            'actif' => 'boolean',
        ];
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function abonnements(): HasMany
    {
        return $this->hasMany(Abonnement::class);
    }
}