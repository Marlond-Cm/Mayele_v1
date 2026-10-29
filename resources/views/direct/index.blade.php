@extends('layouts.app')

@section('titre', 'Cours en direct')

@section('contenu')
<section class="hero hero-compact">
    <div class="conteneur">
        <span class="badge">Classes virtuelles</span>
        <h1>Cours en direct</h1>
        <p class="lead">Retrouve tes enseignants en visioconférence : cours, révisions, questions-réponses. Un mode audio économise ta connexion.</p>
    </div>
</section>

<div class="conteneur section">
    <div class="onglets">
        <a class="onglet {{ $filiere ? '' : 'actif' }}" href="{{ route('direct.index') }}">Toutes les filières</a>
        @foreach ($filieres as $f)
            <a class="onglet {{ $filiere?->id === $f->id ? 'actif' : '' }}"
               href="{{ route('direct.index', ['filiere' => $f->slug]) }}">{{ $f->nom }}</a>
        @endforeach
    </div>

    <h2>À venir</h2>
    <div class="liste-annales">
        @forelse ($aVenir as $c)
            @include('direct._carte', ['c' => $c])
        @empty
            <p class="muted">Aucun cours en direct prévu pour le moment. Reviens bientôt !</p>
        @endforelse
    </div>

    @if ($replays->isNotEmpty())
        <h2 class="semestre-titre">Replays</h2>
        <div class="liste-annales">
            @foreach ($replays as $c)
                @include('direct._carte', ['c' => $c])
            @endforeach
        </div>
    @endif
</div>
@endsection