<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Universite extends Model
{
    protected $fillable = ['nom', 'sigle', 'slug', 'ville', 'type', 'actif'];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    public function etudiants(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function annales(): HasMany
    {
        return $this->hasMany(Annale::class);
    }
}