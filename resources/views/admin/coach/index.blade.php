@extends('layouts.admin')

@section('titre', 'Coach IA')

@section('contenu')
<div class="admin-entete"><h1>Coach IA</h1></div>

<p class="muted">Modèle utilisé : <strong>{{ $modele }}</strong> · Limite : {{ $limite }} messages par jour et par étudiant. Pour convertir les tokens en coût, consulte la tarification dans la Console Anthropic.</p>

<div class="stats">
    <div class="carte stat"><small class="muted">Messages aujourd'hui</small><strong>{{ number_format($stats['aujourdhui'], 0, ',', ' ') }}</strong></div>
    <div class="carte stat"><small class="muted">Messages (7 jours)</small><strong>{{ number_format($stats['sept_jours'], 0, ',', ' ') }}</strong></div>
    <div class="carte stat"><small class="muted">Messages (30 jours)</small><strong>{{ number_format($stats['trente_jours'], 0, ',', ' ') }}</strong></div>
    <div class="carte stat"><small class="muted">Conversations (30 jours)</small><strong>{{ number_format($stats['conversations'], 0, ',', ' ') }}</strong></div>
    <div class="carte stat"><small class="muted">Tokens envoyés (30 jours)</small><strong>{{ number_format($stats['tokens_entree'], 0, ',', ' ') }}</strong></div>
    <div class="carte stat"><small class="muted">Tokens reçus (30 jours)</small><strong>{{ number_format($stats['tokens_sortie'], 0, ',', ' ') }}</strong></div>
</div>

<div class="carte" style="overflow-x:auto">
    <h2>Utilisateurs les plus actifs (7 jours)</h2>
    <table class="tableau">
        <thead><tr><th>Étudiant</th><th>E-mail</th><th>Messages</th></tr></thead>
        <tbody>
            @forelse ($top as $ligne)
                <tr>
                    <td><a href="{{ route('admin.utilisateurs.show', $ligne->id) }}">{{ $ligne->name }}</a></td>
                    <td>{{ $ligne->email }}</td>
                    <td>{{ $ligne->total }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">Aucune utilisation pour le moment.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection