@extends('layouts.app')

@section('titre', 'Nouveautés')

@section('contenu')
@php($icones = ['chapitre' => '📖', 'matiere' => '📚', 'annale' => '📄', 'quiz' => '🎯', 'direct' => '🎥', 'exercice' => '💻'])
<div class="conteneur section" style="max-width:820px">
    <h1 style="font-size:1.9rem">Nouveautés</h1>
    <p class="muted">Les derniers contenus ajoutés pour ta filière.</p>

    <div class="liste-annales">
        @forelse ($liste as $n)
            <a class="nouveaute {{ $n->nouveau ? 'nouveaute-neuve' : '' }}" href="{{ $n->lien }}">
                <span class="nouveaute-icone">{{ $icones[$n->type] ?? '✨' }}</span>
                <span style="flex:1">
                    {{ $n->titre }}<br>
                    <small class="muted">{{ $n->created_at->diffForHumans() }}</small>
                </span>
                @if ($n->nouveau)<span class="badge badge-ok" style="margin:0">Nouveau</span>@endif
            </a>
        @empty
            <div class="carte vide-etat"><p class="muted" style="margin:0">Aucune nouveauté pour le moment. Elles apparaîtront ici dès qu'un contenu sera ajouté.</p></div>
        @endforelse
    </div>
</div>
@endsection