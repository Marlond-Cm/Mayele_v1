<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Le rôle n'est volontairement pas mass-assignable (sécurité).
    protected $fillable = [
        'name', 'email', 'password', 'telephone', 'universite_id',
        'filiere_id', 'niveau', 'code_parrainage', 'parrain_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->code_parrainage)) {
                do {
                    $code = Str::upper(Str::random(8));
                } while (static::where('code_parrainage', $code)->exists());

                $user->code_parrainage = $code;
            }
        });
    }

    public function universite(): BelongsTo
    {
        return $this->belongsTo(Universite::class);
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function parrain(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parrain_id');
    }

    public function filleuls(): HasMany
    {
        return $this->hasMany(User::class, 'parrain_id');
    }

    public function abonnements(): HasMany
    {
        return $this->hasMany(Abonnement::class);
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    public function progressions(): HasMany
    {
        return $this->hasMany(Progression::class);
    }

    public function estAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function aAbonnementActifPour(int $filiereId): bool
    {
        return $this->abonnements()
            ->actif()
            ->whereHas('plan', fn ($q) => $q->where('filiere_id', $filiereId))
            ->exists();
    }
}