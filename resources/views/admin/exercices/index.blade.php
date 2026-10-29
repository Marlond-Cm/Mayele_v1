@extends('layouts.admin')

@section('titre', 'Exercices de code')

@section('contenu')
<div class="admin-entete">
    <h1>Exercices de code</h1>
    <a class="btn btn-primaire" href="{{ route('admin.exercices.create') }}">+ Nouvel exercice</a>
</div>

<form class="filtres" method="GET">
    <select name="langage">
        <option value="">Tous les langages</option>
        @foreach (\App\Models\Exercice::LANGAGES as $cle => $libelle)
            <option value="{{ $cle }}" @selected(request('langage') === $cle)>{{ $libelle }}</option>
        @endforeach
    </select>
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher un exercice">
    <button class="btn btn-contour btn-sm" type="submit">Filtrer</button>
</form>

<div class="carte" style="overflow-x:auto">
    <table class="tableau">
        <thead><tr><th>Ordre</th><th>Exercice</th><th>Langage</th><th>Niveau</th><th>Essais</th><th>Accès</th><th>Statut</th><th></th></tr></thead>
        <tbody>
            @forelse ($exercices as $e)
                <tr>
                    <td>{{ $e->ordre }}</td>
                    <td>{{ $e->titre }}@if ($e->matiere)<br><small class="muted">{{ $e->matiere->nom }}</small>@endif</td>
                    <td>{{ \App\Models\Exercice::LANGAGES[$e->langage] ?? $e->langage }}</td>
                    <td>{{ \App\Models\Exercice::DIFFICULTES[$e->difficulte] ?? $e->difficulte }}</td>
                    <td>{{ $e->progressions_count }}</td>
                    <td><span class="badge {{ $e->est_gratuit ? 'badge-ok' : '' }}">{{ $e->est_gratuit ? 'Gratuit' : 'Abonnés' }}</span></td>
                    <td><span class="badge {{ $e->publie ? 'badge-ok' : 'badge-attente' }}">{{ $e->publie ? 'Publié' : 'Brouillon' }}</span></td>
                    <td>
                        <div class="actions-ligne">
                            <a class="btn btn-primaire btn-sm" target="_blank" href="{{ route('atelier.show', $e->slug) }}">Tester</a>
                            <a class="btn btn-contour btn-sm" href="{{ route('admin.exercices.edit', $e) }}">Modifier</a>
                            <form method="POST" action="{{ route('admin.exercices.destroy', $e) }}" onsubmit="return confirm('Supprimer cet exercice ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted">Aucun exercice.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:1rem">{{ $exercices->links() }}</div>
@endsection