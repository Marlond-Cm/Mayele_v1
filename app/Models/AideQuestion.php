<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AideQuestion extends Model
{
    protected $fillable = [
        'user_id', 'visiteur', 'question', 'reponse', 'source',
        'besoin_support', 'utile', 'tokens_entree', 'tokens_sortie',
    ];

    protected function casts(): array
    {
        return [
            'besoin_support' => 'boolean',
            'utile' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}