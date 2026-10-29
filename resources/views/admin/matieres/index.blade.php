@extends('layouts.admin')

@section('titre', 'Matières')

@section('contenu')
<div class="admin-entete">
    <h1>Matières et cours</h1>
    <a class="btn btn-primaire" href="{{ route('admin.matieres.create') }}">+ Nouvelle matière</a>
</div>

<form class="filtres" method="GET">
    <select name="filiere">
        <option value="">Toutes les filières</option>
        @foreach ($filieres as $filiere)
            <option value="{{ $filiere->id }}" @selected(request('filiere') == $filiere->id)>{{ $filiere->nom }}</option>
        @endforeach
    </select>
    <select name="niveau">
        <option value="">Tous niveaux</option>
        @foreach (['L1', 'L2', 'L3'] as $niveau)
            <option value="{{ $niveau }}" @selected(request('niveau') === $niveau)>{{ $niveau }}</option>
        @endforeach
    </select>
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher une matière">
    <button class="btn btn-contour btn-sm" type="submit">Filtrer</button>
</form>

<div class="carte" style="overflow-x:auto">
    <table class="tableau">
        <thead>
            <tr><th>Filière</th><th>Niv.</th><th>S</th><th>Matière</th><th>Chapitres</th><th>Statut</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($matieres as $matiere)
                <tr>
                    <td>{{ $matiere->filiere->nom }}</td>
                    <td>{{ $matiere->niveau }}</td>
                    <td>{{ $matiere->semestre }}</td>
                    <td>
                        {{ $matiere->nom }}
                        @if ($matiere->parcours)<br><small class="muted">{{ $matiere->parcours }}</small>@endif
                    </td>
                    <td>{{ $matiere->chapitres_count }}</td>
                    <td><span class="badge {{ $matiere->publie ? 'badge-ok' : 'badge-attente' }}">{{ $matiere->publie ? 'Publiée' : 'Masquée' }}</span></td>
                    <td>
                        <div class="actions-ligne">
                            <a class="btn btn-primaire btn-sm" href="{{ route('admin.matieres.chapitres.index', $matiere) }}">Chapitres</a>
                            <a class="btn btn-contour btn-sm" href="{{ route('admin.matieres.edit', $matiere) }}">Modifier</a>
                            <form method="POST" action="{{ route('admin.matieres.destroy', $matiere) }}" onsubmit="return confirm('Supprimer cette matière et tous ses chapitres ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Aucune matière.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:1rem">{{ $matieres->links() }}</div>
@endsection