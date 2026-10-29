@extends('layouts.admin')

@section('titre', 'Universités')

@section('contenu')
<div class="admin-entete">
    <h1>Universités</h1>
    <a class="btn btn-primaire" href="{{ route('admin.universites.create') }}">+ Nouvelle université</a>
</div>

<form class="filtres" method="GET">
    <select name="ville">
        <option value="">Toutes les villes</option>
        @foreach ($villes as $ville)
            <option value="{{ $ville }}" @selected(request('ville') === $ville)>{{ $ville }}</option>
        @endforeach
    </select>
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Nom ou sigle">
    <button class="btn btn-contour btn-sm" type="submit">Filtrer</button>
</form>

<div class="carte" style="overflow-x:auto">
    <table class="tableau">
        <thead><tr><th>Nom</th><th>Sigle</th><th>Ville</th><th>Type</th><th>Étudiants</th><th>Statut</th><th></th></tr></thead>
        <tbody>
            @forelse ($universites as $universite)
                <tr>
                    <td>{{ $universite->nom }}</td>
                    <td>{{ $universite->sigle }}</td>
                    <td>{{ $universite->ville }}</td>
                    <td>{{ $universite->type === 'public' ? 'Publique' : 'Privée' }}</td>
                    <td>{{ $universite->etudiants_count }}</td>
                    <td><span class="badge {{ $universite->actif ? 'badge-ok' : 'badge-attente' }}">{{ $universite->actif ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="actions-ligne">
                            <a class="btn btn-contour btn-sm" href="{{ route('admin.universites.edit', $universite) }}">Modifier</a>
                            <form method="POST" action="{{ route('admin.universites.destroy', $universite) }}" onsubmit="return confirm('Supprimer cette université ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Aucune université.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:1rem">{{ $universites->links() }}</div>
@endsection