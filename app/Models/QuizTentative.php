<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizTentative extends Model
{
    protected $fillable = [
        'user_id', 'quiz_id', 'score', 'bonnes_reponses', 'total_questions', 'reponses', 'termine_le',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'reponses' => 'array',
            'termine_le' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}