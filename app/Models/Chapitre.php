<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapitre extends Model
{
    protected $fillable = [
        'matiere_id', 'titre', 'slug', 'contenu', 'video_url',
        'fichier_pdf', 'duree_minutes', 'ordre', 'est_gratuit', 'publie',
    ];

    protected function casts(): array
    {
        return [
            'duree_minutes' => 'integer',
            'est_gratuit' => 'boolean',
            'publie' => 'boolean',
        ];
    }

    public function scopePublie(Builder $query): Builder
    {
        return $query->where('publie', true);
    }

    public function matiere(): BelongsTo
    {
        return $this->belongsTo(Matiere::class);
    }

    public function progressions(): HasMany
    {
        return $this->hasMany(Progression::class);
    }

    /**
     * Un chapitre gratuit est ouvert à tous ; sinon il faut un abonnement
     * actif sur la filière de la matière.
     */
    public function estAccessiblePour(?User $user): bool
    {
        if ($this->est_gratuit) {
            return true;
        }

        return $user !== null
            && $user->aAbonnementActifPour($this->matiere->filiere_id);
    }

    /**
     * Prépare l'affichage de la vidéo : YouTube (intégration sécurisée)
     * ou fichier direct .mp4 / .webm. Toute autre URL est ignorée.
     *
     * @return array{type: string, url: string}|null
     */
    public function lecteurVideo(): ?array
    {
        if (empty($this->video_url)) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([\w-]{11})~', $this->video_url, $m)) {
            return ['type' => 'youtube', 'url' => 'https://www.youtube-nocookie.com/embed/'.$m[1]];
        }

        if (preg_match('~^https?://.+\.(mp4|webm)(\?.*)?$~i', $this->video_url)) {
            return ['type' => 'fichier', 'url' => $this->video_url];
        }

        return null;
    }
}