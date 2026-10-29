<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CoachMessage extends Model
{
    protected $fillable = ['conversation_id', 'role', 'contenu', 'tokens_entree', 'tokens_sortie'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(CoachConversation::class, 'conversation_id');
    }

    /** Markdown de la réponse vers HTML ; le HTML brut éventuel est supprimé. */
    public function html(): string
    {
        return (string) Str::markdown($this->contenu, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}