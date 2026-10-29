@extends('layouts.app')

@section('titre', $classe->titre)

@section('contenu')
@php($replay = $classe->lecteurReplay())
<div class="conteneur section" style="max-width:860px">
    <p class="fil-ariane"><a href="{{ route('direct.index') }}">Cours en direct</a> › {{ $classe->filiere->nom }}</p>

    <h1>{{ $classe->titre }}</h1>

    <div class="tags" style="margin-bottom:1rem">
        <span class="badge badge-{{ $etat }}">{{ $classe->libelleEtat() }}</span>
        <span class="tag">{{ $classe->debut_le->translatedFormat('l d F Y \à H\hi') }}</span>
        <span class="tag">{{ $classe->duree_minutes }} min</span>
        @if ($classe->matiere)<span class="tag">{{ $classe->matiere->nom }}</span>@endif
        @if ($classe->animateur)<span class="tag">Animé par {{ $classe->animateur->name }}</span>@endif
        <span class="tag {{ $classe->est_gratuit ? 'tag-ok' : '' }}">{{ $classe->est_gratuit ? 'Gratuit' : 'Abonnés' }}</span>
    </div>

    @if (session('erreur'))
        <div class="alerte" style="margin-bottom:1rem">{{ session('erreur') }}</div>
    @endif

    <div class="carte direct-panneau">
        @if ($etat === 'annulee')
            <p><strong>Ce cours a été annulé.</strong></p>
        @elseif ($etat === 'terminee')
            <p><strong>Ce cours est terminé.</strong></p>
        @elseif ($etat === 'a_venir' && ! $moderateur)
            <p class="muted" style="margin:0">La salle ouvre {{ config('visio.ouverture_avant_minutes') }} minutes avant le début.</p>
            <p class="compte-rebours-ligne">Ouverture dans <strong id="compte-rebours" data-ouverture="{{ $ouvertureIso }}" data-id="{{ $classe->id }}">…</strong></p>
        @endif

        @if ($etat !== 'annulee' && ($etat === 'ouverte' || $moderateur))
            @if (! $user)
                <p class="muted">Connecte-toi pour rejoindre le cours.</p>
                <a class="btn btn-primaire" href="{{ route('connexion') }}">Se connecter</a>
                <a class="btn btn-contour" href="{{ route('inscription', ['filiere' => $classe->filiere_id]) }}">Créer un compte</a>
            @elseif (! $accessible)
                <p class="muted">Ce cours en direct est réservé aux abonnés de {{ $classe->filiere->nom }}.</p>
                <a class="btn btn-primaire" href="{{ route('abonnement.index', ['filiere' => $classe->filiere->slug]) }}">S'abonner</a>
            @elseif (! $visioPrete)
                <p class="muted">La visioconférence est en cours de mise en place. Réessaie bientôt.</p>
            @elseif ($peutRejoindre)
                <div class="actions-chapitre" style="margin:0">
                    <a class="btn btn-primaire" href="{{ route('direct.salle', $classe) }}">
                        {{ $moderateur ? '🎓 Animer le cours' : '🎥 Rejoindre le cours' }}
                    </a>
                    <a class="btn btn-contour" href="{{ route('direct.salle', ['classe' => $classe, 'audio' => 1]) }}">🎧 Mode audio (économise ta data)</a>
                </div>
                <p class="muted" style="margin:.8rem 0 0"><small>Utilise un casque si possible. Ton navigateur te demandera l'autorisation d'utiliser le micro et la caméra.</small></p>
            @endif
        @elseif ($etat === 'a_venir' && ! $user)
            <p class="muted">Connecte-toi pour rejoindre le cours à l'ouverture de la salle.</p>
        @endif

        @if ($etat !== 'terminee' && $etat !== 'annulee')
            <div class="actions-chapitre" style="margin:1rem 0 0">
                <a class="btn btn-contour btn-sm" href="{{ route('direct.calendrier', $classe) }}">📅 Ajouter à mon agenda</a>
                <a class="btn btn-contour btn-sm" target="_blank" rel="noopener"
                   href="https://wa.me/?text={{ urlencode('Cours en direct : '.$classe->titre.' le '.$classe->debut_le->translatedFormat('d F à H\hi').' sur '.config('app.name').' : '.route('direct.show', $classe)) }}">Inviter sur WhatsApp</a>
            </div>
        @endif
    </div>

    @if ($classe->description)
        <h2 class="semestre-titre">À propos de ce cours</h2>
        <div class="contenu">{!! \Illuminate\Support\Str::markdown($classe->description, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
    @endif

    @if ($etat === 'terminee' && $classe->replay_url)
        <h2 class="semestre-titre">Replay</h2>
        @if ($replay && $replay['type'] === 'youtube')
            <div class="cadre-video"><iframe src="{{ $replay['url'] }}" title="{{ $classe->titre }}" allow="encrypted-media; picture-in-picture" allowfullscreen loading="lazy"></iframe></div>
        @elseif ($replay)
            <div class="cadre-video"><video controls preload="metadata" src="{{ $replay['url'] }}"></video></div>
        @else
            <a class="btn btn-primaire" href="{{ $classe->replay_url }}" target="_blank" rel="noopener">Revoir l'enregistrement</a>
        @endif
        @unless ($accessible)
            <p class="muted"><small>Le replay est réservé aux abonnés de la filière.</small></p>
        @endunless
    @endif
</div>
@endsection

@push('scripts')
<script>
    const rebours = document.getElementById('compte-rebours');
    if (rebours) {
        const cible = new Date(rebours.dataset.ouverture).getTime();
        const cle = 'direct-recharge-' + rebours.dataset.id;

        const maj = () => {
            const reste = cible - Date.now();
            if (reste <= 0) {
                if (!sessionStorage.getItem(cle)) {
                    sessionStorage.setItem(cle, '1');
                    location.reload();
                }
                rebours.textContent = 'maintenant';
                return;
            }
            const jours = Math.floor(reste / 86400000);
            const heures = Math.floor((reste % 86400000) / 3600000);
            const minutes = Math.floor((reste % 3600000) / 60000);
            rebours.textContent = (jours > 0 ? jours + ' j ' : '') + heures + ' h ' + String(minutes).padStart(2, '0') + ' min';
        };

        maj();
        setInterval(maj, 30000);
    }
</script>
@endpush