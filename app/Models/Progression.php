<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Progression extends Model
{
    protected $fillable = ['user_id', 'chapitre_id', 'termine_le'];

    protected function casts(): array
    {
        return ['termine_le' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chapitre(): BelongsTo
    {
        return $this->belongsTo(Chapitre::class);
    }
}