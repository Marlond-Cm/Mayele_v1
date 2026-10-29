<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = [
        'matiere_id', 'titre', 'description', 'points_par_question', 'est_gratuit', 'publie',
    ];

    protected function casts(): array
    {
        return [
            'points_par_question' => 'integer',
            'est_gratuit' => 'boolean',
            'publie' => 'boolean',
        ];
    }

    public function matiere(): BelongsTo
    {
        return $this->belongsTo(Matiere::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('ordre');
    }

    public function tentatives(): HasMany
    {
        return $this->hasMany(QuizTentative::class);
    }

    public function estAccessiblePour(?User $user): bool
    {
        if ($this->est_gratuit) {
            return true;
        }

        $this->loadMissing('matiere');

        return $user !== null && $user->aAbonnementActifPour($this->matiere->filiere_id);
    }
}