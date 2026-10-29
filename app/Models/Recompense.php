<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recompense extends Model
{
    protected $fillable = ['user_id', 'type', 'source_id', 'jours', 'abonnement_id'];

    protected function casts(): array
    {
        return ['jours' => 'integer'];
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