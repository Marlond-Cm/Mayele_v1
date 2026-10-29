@extends('layouts.admin')

@section('titre', 'Aide')

@section('contenu')
<div class="admin-entete"><h1>Aide et assistant</h1></div>

<p class="muted">Statistiques sur 30 jours. Pour ajouter ou corriger une réponse, modifie le fichier <code>config/aide.php</code>, puis redéploie. Les questions des utilisateurs sont enregistrées : ne les partage pas.</p>

<div class="stats">
    <div class="carte stat"><small class="muted">Questions posées</small><strong>{{ number_format($stats['questions'], 0, ',', ' ') }}</strong></div>
    <div class="carte stat"><small class="muted">Répondues par l'IA</small><strong>{{ number_format($stats['ia'], 0, ',', ' ') }}</strong></div>
    <div class="carte stat"><small class="muted">Renvoyées au support</small><strong>{{ number_format($stats['support'], 0, ',', ' ') }}</strong></div>
    <div class="carte stat"><small class="muted">Avis 👍</small><strong>{{ $stats['utiles'] }}</strong></div>
    <div class="carte stat"><small class="muted">Avis 👎</small><strong>{{ $stats['pas_utiles'] }}</strong></div>
    <div class="carte stat"><small class="muted">Tokens envoyés / reçus</small><strong style="font-size:1.2rem">{{ number_format($stats['tokens_entree'], 0, ',', ' ') }} / {{ number_format($stats['tokens_sortie'], 0, ',', ' ') }}</strong></div>
</div>

<div class="grille" style="grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); margin-bottom:1.5rem">
    <div class="carte" style="overflow-x:auto">
        <h2>Sans réponse : à ajouter à la FAQ</h2>
        <table class="tableau">
            <thead><tr><th>Date</th><th>Question</th></tr></thead>
            <tbody>
                @forelse ($sansReponse as $q)
                    <tr><td>{{ $q->created_at->translatedFormat('d/m H:i') }}</td><td>{{ \Illuminate\Support\Str::limit($q->question, 140) }}</td></tr>
                @empty
                    <tr><td colspan="2" class="muted">Rien pour le moment.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="carte" style="overflow-x:auto">
        <h2>Réponses jugées inutiles 👎</h2>
        <table class="tableau">
            <thead><tr><th>Question</th><th>Réponse donnée</th></tr></thead>
            <tbody>
                @forelse ($pasUtiles as $q)
                    <tr>
                        <td>{{ \Illuminate\Support\Str::limit($q->question, 100) }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($q->reponse, 140) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="muted">Rien pour le moment.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="carte" style="overflow-x:auto">
    <h2>Dernières questions</h2>
    <table class="tableau">
        <thead><tr><th>Date</th><th>Qui</th><th>Question</th><th>Source</th><th>Avis</th></tr></thead>
        <tbody>
            @forelse ($recentes as $q)
                <tr>
                    <td>{{ $q->created_at->translatedFormat('d/m H:i') }}</td>
                    <td>{{ $q->user?->name ?? 'Visiteur' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($q->question, 120) }}</td>
                    <td><span class="badge {{ $q->source === 'ia' ? 'badge-ok' : 'badge-attente' }}">{{ $q->source === 'ia' ? 'IA' : 'Articles' }}</span></td>
                    <td>{{ $q->utile === null ? '' : ($q->utile ? '👍' : '👎') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Aucune question pour le moment.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:1rem">{{ $recentes->links() }}</div>
@endsection