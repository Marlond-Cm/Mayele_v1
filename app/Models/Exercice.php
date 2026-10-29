<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercice extends Model
{
    public const LANGAGES = [
        'python' => 'Python',
        'javascript' => 'JavaScript',
        'sql' => 'SQL',
        'html' => 'HTML',
    ];

    public const DIFFICULTES = [
        'facile' => 'Facile',
        'moyen' => 'Moyen',
        'difficile' => 'Difficile',
    ];

    protected $fillable = [
        'filiere_id', 'matiere_id', 'chapitre_id', 'titre', 'slug', 'enonce', 'langage', 'code_depart',
        'tests', 'indice', 'solution', 'difficulte', 'ordre', 'est_gratuit', 'publie',
    ];

    protected function casts(): array
    {
        return [
            'tests' => 'array',
            'ordre' => 'integer',
            'est_gratuit' => 'boolean',
            'publie' => 'boolean',
        ];
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function matiere(): BelongsTo
    {
        return $this->belongsTo(Matiere::class);
    }

    public function chapitre(): BelongsTo
    {
        return $this->belongsTo(Chapitre::class);
    }

    public function progressions(): HasMany
    {
        return $this->hasMany(ExerciceProgression::class);
    }

    public function estAccessiblePour(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->estAdmin() || $this->est_gratuit || $user->aAbonnementActifPour($this->filiere_id);
    }
}