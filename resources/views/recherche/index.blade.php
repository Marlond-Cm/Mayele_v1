@extends('layouts.app')

@section('titre', $q !== '' ? 'Recherche : '.$q : 'Recherche')

@section('contenu')
<div class="conteneur section">
    <h1 style="font-size:1.9rem">Rechercher</h1>

    <form action="{{ route('recherche') }}" method="GET" class="catalogue-outils" role="search" style="justify-content:flex-start">
        <input type="search" name="q" value="{{ $q }}" placeholder="Matière, chapitre, sujet…" autofocus style="flex:1;max-width:520px" aria-label="Rechercher">
        <button class="btn btn-primaire" type="submit">Rechercher</button>
    </form>

    @if ($q === '' || mb_strlen($q) < 2)
        <p class="muted">Tape au moins 2 lettres : le nom d'une matière, d'un chapitre ou d'un sujet.</p>
    @elseif ($matieres->isEmpty() && $chapitres->isEmpty())
        <div class="carte vide-etat">
            <h3>Aucun résultat pour « {{ $q }} »</h3>
            <p class="muted">Vérifie l'orthographe ou essaie un mot plus court. Tu peux aussi poser ta question à l'assistant d'aide.</p>
            <a class="btn btn-primaire" href="{{ route('aide') }}">Ouvrir l'aide</a>
        </div>
    @else
        @if ($matieres->isNotEmpty())
            <h2 class="semestre-titre">Matières ({{ $matieres->count() }})</h2>
            <div class="grille-cours">
                @foreach ($matieres as $matiere)
                    <x-matiere-carte :matiere="$matiere" :theme="$matiere->filiere->theme" :total="$matiere->chapitres_total" />
                @endforeach
            </div>
        @endif

        @if ($chapitres->isNotEmpty())
            <h2 class="semestre-titre">Chapitres ({{ $chapitres->count() }})</h2>
            <ul class="liste-chapitres">
                @foreach ($chapitres as $chapitre)
                    <li>
                        <a class="ligne-chapitre" href="{{ route('chapitres.show', [$chapitre->matiere->slug, $chapitre->slug]) }}">
                            <span class="num">›</span>
                            <span class="titre">{{ $chapitre->titre }}<br><small class="muted">{{ $chapitre->matiere->nom }} · {{ $chapitre->matiere->filiere->nom }}</small></span>
                            @if ($chapitre->est_gratuit)<span class="badge badge-gratuit" style="margin:0">Gratuit</span>@endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</div>
@endsection