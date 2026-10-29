@extends('layouts.admin')

@section('titre', 'Cours en direct')

@section('contenu')
<div class="admin-entete">
    <h1>Cours en direct</h1>
    <a class="btn btn-primaire" href="{{ route('admin.direct.create') }}">+ Planifier un cours</a>
</div>

<div class="onglets">
    <a class="onglet {{ request('quand') !== 'passes' ? 'actif' : '' }}" href="{{ route('admin.direct.index') }}">À venir</a>
    <a class="onglet {{ request('quand') === 'passes' ? 'actif' : '' }}" href="{{ route('admin.direct.index', ['quand' => 'passes']) }}">Passés</a>
</div>

<div class="carte" style="overflow-x:auto">
    <table class="tableau">
        <thead><tr><th>Date</th><th>Cours</th><th>Filière</th><th>Animateur</th><th>Présents</th><th>Statut</th><th></th></tr></thead>
        <tbody>
            @forelse ($classes as $c)
                <tr>
                    <td>{{ $c->debut_le->translatedFormat('d/m/Y H\hi') }}<br><small class="muted">{{ $c->duree_minutes }} min</small></td>
                    <td>{{ $c->titre }}@if ($c->matiere)<br><small class="muted">{{ $c->matiere->nom }}</small>@endif</td>
                    <td>{{ $c->filiere->nom }}<br><small class="muted">{{ $c->est_gratuit ? 'Gratuit' : 'Abonnés' }}</small></td>
                    <td>{{ $c->animateur?->name }}</td>
                    <td>{{ $c->presences_count }}</td>
                    <td><span class="badge badge-{{ $c->etat() }}">{{ $c->libelleEtat() }}</span></td>
                    <td>
                        <div class="actions-ligne">
                            @if ($c->statut === 'planifie')
                                <a class="btn btn-primaire btn-sm" target="_blank" href="{{ route('direct.salle', $c) }}">Ouvrir la salle</a>
                            @endif
                            <a class="btn btn-contour btn-sm" href="{{ route('admin.direct.presences', $c) }}">Présences</a>
                            <a class="btn btn-contour btn-sm" href="{{ route('admin.direct.edit', $c) }}">Modifier</a>
                            <form method="POST" action="{{ route('admin.direct.destroy', $c) }}" onsubmit="return confirm('Supprimer ce cours et ses présences ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Aucun cours.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:1rem">{{ $classes->links() }}</div>
@endsection