<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Throwable;

class PaiementJournal extends Model
{
    protected $table = 'paiement_journal';

    protected $fillable = ['paiement_id', 'type', 'message', 'details'];

    protected function casts(): array
    {
        return ['details' => 'array'];
    }

    public function paiement(): BelongsTo
    {
        return $this->belongsTo(Paiement::class);
    }

    /** Une écriture dans le journal ne doit jamais bloquer un paiement. */
    public static function noter(?Paiement $paiement, string $type, string $message, ?array $details = null): void
    {
        try {
            static::create([
                'paiement_id' => $paiement?->id,
                'type' => $type,
                'message' => Str::limit($message, 250, '…'),
                'details' => $details,
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }
}