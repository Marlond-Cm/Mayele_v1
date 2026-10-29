<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciceProgression extends Model
{
    protected $fillable = ['exercice_id', 'user_id', 'code', 'tentatives', 'reussi', 'reussi_le', 'solution_vue'];

    protected function casts(): array
    {
        return [
            'tentatives' => 'integer',
            'reussi' => 'boolean',
            'reussi_le' => 'datetime',
            'solution_vue' => 'boolean',
        ];
    }

    public function exercice(): BelongsTo
    {
        return $this->belongsTo(Exercice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function solutionDisponible(): bool
    {
        return $this->reussi || $this->tentatives >= 2;
    }
}