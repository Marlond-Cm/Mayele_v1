<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Matiere extends Model
{
    protected $fillable = [
        'filiere_id', 'niveau', 'semestre', 'parcours',
        'nom', 'slug', 'description', 'ordre', 'publie',
    ];

    protected function casts(): array
    {
        return [
            'semestre' => 'integer',
            'publie' => 'boolean',
        ];
    }

    public function scopePubliee(Builder $query): Builder
    {
        return $query->where('publie', true);
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function chapitres(): HasMany
    {
        return $this->hasMany(Chapitre::class)->orderBy('ordre');
    }

    public function annales(): HasMany
    {
        return $this->hasMany(Annale::class);
    }
}