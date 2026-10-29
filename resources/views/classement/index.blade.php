@extends('layouts.app')

@section('titre', 'Classement')

@section('contenu')
<section class="hero hero-compact">
    <div class="conteneur">
        <span class="badge">Quiz</span>
        <h1>Quelle université est la meilleure ?</h1>
        <p class="lead">Chaque quiz rapporte des points, seul ton meilleur score par quiz compte. Le score d'une université est la somme des points de ses 10 meilleurs étudiants.</p>
        @auth
            @if ($monRang)
                <p><strong>Ton rang : #{{ $monRang }}</strong> avec {{ $mesPoints }} points</p>
            @else
                <p class="muted">Joue un quiz pour entrer dans le classement.</p>
            @endif
        @endauth
    </div>
</section>

<div class="conteneur section">
    <div class="onglets">
        @foreach (['semaine' => 'Cette semaine', 'mois' => 'Ce mois', 'tout' => 'Depuis le début'] as $cle => $libelle)
            <a class="onglet {{ $periode === $cle ? 'actif' : '' }}"
               href="{{ route('classement', array_filter(['periode' => $cle, 'filiere' => $filiere?->slug])) }}">{{ $libelle }}</a>
        @endforeach
    </div>
    <div class="onglets">
        <a class="onglet {{ $filiere ? '' : 'actif' }}" href="{{ route('classement', ['periode' => $periode]) }}">Toutes les filières</a>
        @foreach ($filieres as $f)
            <a class="onglet {{ $filiere?->id === $f->id ? 'actif' : '' }}"
               href="{{ route('classement', ['periode' => $periode, 'filiere' => $f->slug]) }}">{{ $f->nom }}</a>
        @endforeach
    </div>

    <div class="classements">
        <div class="carte">
            <h2>Universités</h2>
            @forelse ($universites as $u)
                <div class="rang">
                    <span class="pos">{{ $loop->iteration }}</span>
                    <span class="nom">{{ $u['nom'] }}<small>{{ $u['participants'] }} participant{{ $u['participants'] > 1 ? 's' : '' }}</small></span>
                    <strong>{{ number_format($u['total'], 0, ',', ' ') }} pts</strong>
                </div>
            @empty
                <p class="muted">Pas encore de points sur cette période. Sois le premier !</p>
            @endforelse
        </div>

        <div class="carte">
            <h2>Meilleurs étudiants</h2>
            @forelse ($etudiants as $e)
                <div class="rang {{ $e['moi'] ? 'moi' : '' }}">
                    <span class="pos">{{ $loop->iteration }}</span>
                    <span class="nom">{{ $e['nom'] }}<small>{{ $e['universite'] }}</small></span>
                    <strong>{{ number_format($e['points'], 0, ',', ' ') }} pts</strong>
                </div>
            @empty
                <p class="muted">Pas encore de points sur cette période.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection