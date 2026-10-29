@extends('layouts.app')

@section('titre', 'Coach IA · '.$conversation->titre)

@section('contenu')
@php
    $modeLibelle = \App\Models\CoachConversation::MODES[$conversation->mode] ?? $conversation->mode;
    $suggestions = [
        'libre' => ['Résume ce chapitre en 5 points', 'Je ne comprends pas : '],
        'expliquer' => ['Explique-moi ce chapitre simplement', 'Donne-moi un exemple concret au Congo', 'Quelles erreurs fréquentes à l\'examen ?'],
        'corriger' => ['Voici l\'exercice : ... Et voici ma réponse : ...'],
        'interroger' => ['Pose-moi ta première question', 'Interroge-moi sur les points difficiles'],
    ][$conversation->mode] ?? [];
@endphp

<div class="conteneur section" style="max-width:820px">
    <p class="fil-ariane">
        <a href="{{ route('coach.index') }}">Coach IA</a> ›
        <a href="{{ route('matieres.show', $conversation->matiere->slug) }}">{{ $conversation->matiere->nom }}</a>
    </p>

    <h1>{{ $conversation->titre }}</h1>
    <div class="tags">
        <span class="tag tag-ok">{{ $modeLibelle }}</span>
        <span class="tag" id="quota">{{ $restant }} message(s) restant(s) aujourd'hui</span>
    </div>

    <div class="chat" id="chat">
        @forelse ($conversation->messages as $message)
            @if ($message->role === 'user')
                <div class="bulle bulle-moi">{{ $message->contenu }}</div>
            @else
                <div class="bulle bulle-coach contenu">{!! $message->html() !!}</div>
            @endif
        @empty
            <div class="bulle bulle-coach">Salut ! Je suis ton coach pour « {{ $conversation->titre }} ». Pose-moi ta question ou choisis une suggestion ci-dessous.</div>
        @endforelse
    </div>

    @if ($autorise)
        @if ($suggestions)
            <div class="chips">
                @foreach ($suggestions as $suggestion)
                    <button type="button" class="chip" data-suggestion="{{ $suggestion }}">{{ $suggestion }}</button>
                @endforeach
            </div>
        @endif

        <form id="form-coach" method="POST" action="{{ route('coach.envoyer', $conversation) }}" class="chat-saisie">
            @csrf
            <textarea name="message" id="message" rows="3" maxlength="{{ config('mayele.coach.max_caracteres') }}" placeholder="Écris ta question…" required></textarea>
            <button type="submit" class="btn btn-primaire" id="envoyer">Envoyer</button>
        </form>
    @else
        <div class="carte verrou">
            <div class="icone">🔒</div>
            <h2>Abonnement expiré</h2>
            <p class="muted">Renouvelle ton abonnement pour continuer avec le coach.</p>
            <a class="btn btn-primaire" href="{{ route('abonnement.index', ['filiere' => $conversation->matiere->filiere->slug]) }}">S'abonner</a>
        </div>
    @endif

    <p class="muted" style="margin-top:1rem"><small>Le coach peut se tromper : vérifie toujours avec ton cours. Tes échanges sont enregistrés pour améliorer le service.</small></p>
</div>
@endsection

@push('scripts')
<script>
    const chat = document.getElementById('chat');
    const form = document.getElementById('form-coach');
    const champ = document.getElementById('message');
    const bouton = document.getElementById('envoyer');
    const quota = document.getElementById('quota');

    chat.scrollTop = chat.scrollHeight;

    document.querySelectorAll('[data-suggestion]').forEach((puce) => {
        puce.addEventListener('click', () => {
            champ.value = puce.dataset.suggestion;
            champ.focus();
        });
    });

    function ajouter(classes, contenu, html = false) {
        const bulle = document.createElement('div');
        bulle.className = 'bulle ' + classes;
        if (html) {
            bulle.classList.add('contenu');
            bulle.innerHTML = contenu; // HTML déjà nettoyé côté serveur
        } else {
            bulle.textContent = contenu;
        }
        chat.appendChild(bulle);
        chat.scrollTop = chat.scrollHeight;
        return bulle;
    }

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const message = champ.value.trim();
            if (message.length < 2) return;

            const donnees = new FormData(form);
            ajouter('bulle-moi', message);
            champ.value = '';
            champ.disabled = true;
            bouton.disabled = true;

            const attente = ajouter('bulle-coach bulle-attente', 'Le coach réfléchit…');

            try {
                const reponse = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: donnees,
                });
                const data = await reponse.json().catch(() => ({}));

                if (!reponse.ok) {
                    attente.className = 'bulle bulle-coach bulle-erreur';
                    attente.textContent = (data.errors && data.errors.message ? data.errors.message[0] : data.message)
                        || 'Une erreur est survenue. Recharge la page et réessaie.';
                    champ.value = message;
                } else {
                    attente.remove();
                    ajouter('bulle-coach', data.html, true);
                    quota.textContent = data.restant + ' message(s) restant(s) aujourd\'hui';
                }
            } catch (err) {
                attente.className = 'bulle bulle-coach bulle-erreur';
                attente.textContent = 'Connexion impossible. Vérifie ton réseau et réessaie.';
                champ.value = message;
            } finally {
                champ.disabled = false;
                bouton.disabled = false;
                champ.focus();
            }
        });
    }
</script>
@endpush