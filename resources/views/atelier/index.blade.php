@extends('layouts.app')

@section('titre', 'Atelier de code')

@section('contenu')
@php
    $reussis = $progressions->where('reussi', true)->count();
    $total = $exercices->count();
    $pct = $total > 0 ? (int) round($reussis / $total * 100) : 0;
    $icones = ['python' => '🐍', 'javascript' => '🟨', 'sql' => '🗄️', 'html' => '🌐'];
@endphp

<section class="hero hero-compact">
    <div class="conteneur">
        <span class="badge">Atelier de code</span>
        <h1>Pratique ce que tu apprends</h1>
        <p class="lead">Écris du code directement dans ton navigateur, lance-le et vérifie ta solution avec des tests automatiques. Python, JavaScript, SQL et HTML.</p>
        @auth
            <p style="margin:0"><strong>{{ $reussis }} / {{ $total }}</strong> exercices réussis ({{ $pct }} %)</p>
        @endauth
    </div>
</section>

<div class="conteneur section">
    <div class="onglets">
        <a class="onglet {{ $langage ? '' : 'actif' }}" href="{{ route('atelier.index', array_filter(['difficulte' => $difficulte])) }}">Tous</a>
        @foreach (\App\Models\Exercice::LANGAGES as $cle => $libelle)
            <a class="onglet {{ $langage === $cle ? 'actif' : '' }}"
               href="{{ route('atelier.index', array_filter(['langage' => $cle, 'difficulte' => $difficulte])) }}">{{ $icones[$cle] }} {{ $libelle }}</a>
        @endforeach
    </div>
    <div class="onglets">
        @foreach (['' => 'Tous niveaux'] + \App\Models\Exercice::DIFFICULTES as $cle => $libelle)
            <a class="onglet {{ ($difficulte ?? '') === (string) $cle ? 'actif' : '' }}"
               href="{{ route('atelier.index', array_filter(['langage' => $langage, 'difficulte' => $cle])) }}">{{ $libelle }}</a>
        @endforeach
    </div>

    <div class="grille-cours">
        @forelse ($exercices as $e)
            @php
                $p = $progressions->get($e->id);
                $accessible = $e->est_gratuit || ($user && ($user->estAdmin() || in_array($e->filiere_id, $abonnes, true)));
            @endphp
            <a class="cours-carte" data-theme="{{ $e->filiere->theme }}" href="{{ $user ? route('atelier.show', $e->slug) : route('connexion') }}">
                <div class="cours-couverture">
                    <span class="cours-icone" aria-hidden="true">{{ $icones[$e->langage] ?? '💻' }}</span>
                    <span class="cours-niveau">{{ \App\Models\Exercice::LANGAGES[$e->langage] }} · {{ \App\Models\Exercice::DIFFICULTES[$e->difficulte] }}</span>
                </div>
                <div class="cours-corps">
                    <h3>{{ $e->titre }}</h3>
                    @if ($e->matiere)<small>{{ $e->matiere->nom }}</small>@endif
                    <div class="cours-pied">
                        @if ($p?->reussi)
                            <span class="badge badge-ok" style="margin:0">✓ Réussi</span>
                        @elseif ($p)
                            <span class="badge badge-attente" style="margin:0">En cours · {{ $p->tentatives }} essai{{ $p->tentatives > 1 ? 's' : '' }}</span>
                        @elseif (! $accessible)
                            <span class="badge" style="margin:0">🔒 Abonnés</span>
                        @else
                            <span class="badge badge-gratuit" style="margin:0">{{ $e->est_gratuit ? 'Gratuit' : 'À faire' }}</span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="carte vide-etat" style="grid-column:1/-1"><p class="muted" style="margin:0">Aucun exercice pour ce filtre.</p></div>
        @endforelse
    </div>

    @guest
        <p class="muted" style="margin-top:1.5rem"><a href="{{ route('inscription') }}">Crée un compte</a> ou <a href="{{ route('connexion') }}">connecte-toi</a> pour ouvrir les exercices et sauvegarder ta progression.</p>
    @endguest
</div>
@endsection