<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassePresence extends Model
{
    protected $fillable = ['classe_virtuelle_id', 'user_id', 'premiere_entree_at', 'derniere_presence_at'];

    protected function casts(): array
    {
        return [
            'premiere_entree_at' => 'datetime',
            'derniere_presence_at' => 'datetime',
        ];
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(ClasseVirtuelle::class, 'classe_virtuelle_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function minutes(): int
    {
        return max(1, (int) round($this->premiere_entree_at->diffInMinutes($this->derniere_presence_at, true)));
    }
}