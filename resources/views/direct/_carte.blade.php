@php($accessible = $c->est_gratuit || ($user && ($user->estAdmin() || in_array($c->filiere_id, $abonnes, true))))
<article class="direct-carte" data-theme="{{ $c->filiere->theme }}">
    <div class="direct-date">
        <strong>{{ $c->debut_le->translatedFormat('d') }}</strong>
        <span>{{ $c->debut_le->translatedFormat('M') }}</span>
    </div>

    <div class="direct-infos">
        <h3><a href="{{ route('direct.show', $c) }}">{{ $c->titre }}</a></h3>
        <div class="tags">
            <span class="tag">{{ $c->debut_le->translatedFormat('H\hi') }} · {{ $c->duree_minutes }} min</span>
            <span class="tag">{{ $c->filiere->nom }}</span>
            @if ($c->matiere)<span class="tag">{{ $c->matiere->nom }}</span>@endif
            @if ($c->animateur)<span class="tag">{{ $c->animateur->name }}</span>@endif
            <span class="tag {{ $c->est_gratuit ? 'tag-ok' : '' }}">{{ $c->est_gratuit ? 'Gratuit' : ($accessible ? 'Inclus' : '🔒 Abonnés') }}</span>
        </div>
    </div>

    <div class="direct-actions">
        <span class="badge badge-{{ $c->etat() }}">{{ $c->libelleEtat() }}</span>
        <a class="btn btn-primaire btn-sm" href="{{ route('direct.show', $c) }}">
            {{ $c->etat() === 'ouverte' ? 'Rejoindre' : ($c->etat() === 'terminee' ? 'Revoir' : 'Détails') }}
        </a>
    </div>
</article>