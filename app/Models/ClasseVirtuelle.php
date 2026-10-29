<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class ClasseVirtuelle extends Model
{
    protected $table = 'classes_virtuelles';

    protected $fillable = [
        'filiere_id', 'matiere_id', 'animateur_id', 'titre', 'description', 'salle',
        'debut_le', 'duree_minutes', 'est_gratuit', 'replay_url', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'debut_le' => 'datetime',
            'duree_minutes' => 'integer',
            'est_gratuit' => 'boolean',
        ];
    }

    protected function finLe(): Attribute
    {
        return Attribute::get(fn (): Carbon => $this->debut_le->copy()->addMinutes($this->duree_minutes));
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function matiere(): BelongsTo
    {
        return $this->belongsTo(Matiere::class);
    }

    public function animateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'animateur_id');
    }

    public function presences(): HasMany
    {
        return $this->hasMany(ClassePresence::class);
    }

    /** Cours à venir ou encore ouverts (jusqu'à la fermeture de la salle). */
    public function scopeAVenir(Builder $query): Builder
    {
        return $query->whereRaw(
            'DATE_ADD(debut_le, INTERVAL (duree_minutes + ?) MINUTE) >= ?',
            [(int) config('visio.fermeture_apres_minutes'), now()->toDateTimeString()]
        );
    }

    public function scopeTerminees(Builder $query): Builder
    {
        return $query->whereRaw(
            'DATE_ADD(debut_le, INTERVAL (duree_minutes + ?) MINUTE) < ?',
            [(int) config('visio.fermeture_apres_minutes'), now()->toDateTimeString()]
        );
    }

    public static function prochaine(int $filiereId): ?self
    {
        return static::query()
            ->where('filiere_id', $filiereId)
            ->where('statut', 'planifie')
            ->aVenir()
            ->orderBy('debut_le')
            ->first();
    }

    /** annulee | a_venir | ouverte | terminee */
    public function etat(): string
    {
        if ($this->statut === 'annule') {
            return 'annulee';
        }

        $maintenant = now();
        $ouverture = $this->debut_le->copy()->subMinutes((int) config('visio.ouverture_avant_minutes'));
        $fermeture = $this->fin_le->copy()->addMinutes((int) config('visio.fermeture_apres_minutes'));

        if ($maintenant->lt($ouverture)) {
            return 'a_venir';
        }

        return $maintenant->lte($fermeture) ? 'ouverte' : 'terminee';
    }

    public function enDirect(): bool
    {
        return $this->statut !== 'annule' && now()->between($this->debut_le, $this->fin_le);
    }

    public function libelleEtat(): string
    {
        return match ($this->etat()) {
            'annulee' => 'Annulé',
            'a_venir' => 'À venir',
            'ouverte' => $this->enDirect() ? '🔴 En direct' : 'Salle ouverte',
            default => 'Terminé',
        };
    }

    /** L'animateur et les administrateurs sont modérateurs de la salle. */
    public function estModerateur(?User $user): bool
    {
        return $user !== null && ($user->estAdmin() || $user->id === $this->animateur_id);
    }

    public function estAccessiblePour(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->estModerateur($user)
            || $this->est_gratuit
            || $user->aAbonnementActifPour($this->filiere_id);
    }

    /** Lecteur du replay : YouTube (intégration sécurisée) ou fichier direct. */
    public function lecteurReplay(): ?array
    {
        if (empty($this->replay_url)) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([\w-]{11})~', $this->replay_url, $m)) {
            return ['type' => 'youtube', 'url' => 'https://www.youtube-nocookie.com/embed/'.$m[1]];
        }

        if (preg_match('~^https?://.+\.(mp4|webm)(\?.*)?$~i', $this->replay_url)) {
            return ['type' => 'fichier', 'url' => $this->replay_url];
        }

        return null;
    }
}