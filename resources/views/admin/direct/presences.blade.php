@extends('layouts.admin')

@section('titre', 'Présences')

@section('contenu')
<div class="admin-entete">
    <div>
        <p class="fil-ariane"><a href="{{ route('admin.direct.index') }}">← Cours en direct</a></p>
        <h1>{{ $classe->titre }}</h1>
        <p class="muted">{{ $classe->debut_le->translatedFormat('d F Y à H\hi') }} · {{ $classe->filiere->nom }}</p>
    </div>
</div>

<div class="stats">
    <div class="carte stat"><small class="muted">Participants</small><strong>{{ $presences->count() }}</strong></div>
    <div class="carte stat"><small class="muted">Durée moyenne</small><strong>{{ $moyenne }} min</strong></div>
</div>

<div class="carte" style="overflow-x:auto">
    <table class="tableau">
        <thead><tr><th>Étudiant</th><th>Contact</th><th>Entré à</th><th>Dernier signe</th><th>Durée</th></tr></thead>
        <tbody>
            @forelse ($presences as $p)
                <tr>
                    <td><a href="{{ route('admin.utilisateurs.show', $p->user) }}">{{ $p->user->name }}</a></td>
                    <td>{{ $p->user->email }}<br><small class="muted">{{ $p->user->telephone }}</small></td>
                    <td>{{ $p->premiere_entree_at->translatedFormat('H\hi') }}</td>
                    <td>{{ $p->derniere_presence_at->translatedFormat('H\hi') }}</td>
                    <td>{{ $p->minutes() }} min</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Personne ne s'est connecté.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection