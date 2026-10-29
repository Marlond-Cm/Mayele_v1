<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoachConversation extends Model
{
    public const MODES = [
        'libre' => 'Question libre',
        'expliquer' => 'Expliquer',
        'corriger' => 'Corriger mon exercice',
        'interroger' => 'M\'interroger',
    ];

    protected $fillable = ['user_id', 'matiere_id', 'chapitre_id', 'mode', 'titre'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function matiere(): BelongsTo
    {
        return $this->belongsTo(Matiere::class);
    }

    public function chapitre(): BelongsTo
    {
        return $this->belongsTo(Chapitre::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CoachMessage::class, 'conversation_id')->orderBy('id');
    }
}