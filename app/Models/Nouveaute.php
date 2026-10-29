<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Nouveaute extends Model
{
    protected $fillable = ['filiere_id', 'type', 'titre', 'lien'];

    public function scopeVisiblesPour(Builder $query, User $user): Builder
    {
        return $query->where(
            fn ($w) => $w->whereNull('filiere_id')->orWhere('filiere_id', $user->filiere_id)
        );
    }

    /** Nombre de nouveautés apparues depuis la dernière visite de l'étudiant. */
    public static function nonLuesPour(User $user): int
    {
        return static::query()
            ->visiblesPour($user)
            ->where('created_at', '>', $user->nouveautes_vues_at ?? $user->created_at)
            ->count();
    }

    public static function annoncer(?int $filiereId, string $type, string $titre, string $lien): void
    {
        $dejaAnnoncee = static::query()
            ->where('type', $type)
            ->where('lien', $lien)
            ->where('created_at', '>=', now()->subHours(6))
            ->exists();

        if ($dejaAnnoncee) {
            return;
        }

        static::create(['filiere_id' => $filiereId, 'type' => $type, 'titre' => $titre, 'lien' => $lien]);
    }
}