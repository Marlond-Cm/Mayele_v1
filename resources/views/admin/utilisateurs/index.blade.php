@extends('layouts.admin')

@section('titre', 'Utilisateurs')

@section('contenu')
<div class="admin-entete"><h1>Utilisateurs</h1></div>

<form class="filtres" method="GET">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Nom, e-mail, téléphone">
    <label class="case"><input type="checkbox" name="abonne" value="1" @checked(request('abonne') === '1')> Abonnés actifs seulement</label>
    <button class="btn btn-contour btn-sm" type="submit">Filtrer</button>
</form>

<div class="carte" style="overflow-x:auto">
    <table class="tableau">
        <thead><tr><th>Nom</th><th>Contact</th><th>Université</th><th>Filière</th><th>Abonnement</th><th>Inscrit le</th><th></th></tr></thead>
        <tbody>
            @forelse ($utilisateurs as $u)
                <tr>
                    <td>{{ $u->name }}@if ($u->role === 'admin') <span class="badge">Admin</span>@endif</td>
                    <td>{{ $u->email }}<br><small class="muted">{{ $u->telephone }}</small></td>
                    <td>{{ $u->universite?->sigle ?? $u->universite?->nom }}</td>
                    <td>{{ $u->filiere?->nom }} {{ $u->niveau }}</td>
                    <td><span class="badge {{ $u->abonnements_actifs > 0 ? 'badge-ok' : 'badge-attente' }}">{{ $u->abonnements_actifs > 0 ? 'Actif' : 'Aucun' }}</span></td>
                    <td>{{ $u->created_at->translatedFormat('d/m/Y') }}</td>
                    <td><a class="btn btn-contour btn-sm" href="{{ route('admin.utilisateurs.show', $u) }}">Détails</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Aucun utilisateur.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:1rem">{{ $utilisateurs->links() }}</div>
@endsection