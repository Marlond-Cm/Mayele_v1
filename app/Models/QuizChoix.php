<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizChoix extends Model
{
    protected $table = 'quiz_choix';

    protected $fillable = ['question_id', 'libelle', 'est_correct', 'ordre'];

    protected function casts(): array
    {
        return ['est_correct' => 'boolean'];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}