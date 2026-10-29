@extends('layouts.admin')

@section('titre', 'Surveillance des paiements')

@section('contenu')
<div class="admin-entete">
    <h1>Surveillance des paiements</h1>
    <form method="POST" action="{{ route('admin.surveillance.verifier') }}">
        @csrf
        <button class="btn btn-primaire" type="submit">Vérifier maintenant</button>
    </form>
</div>

<p class="muted">Le contrôle automatique tourne toutes les 10 minutes. Il récupère les paiements dont le webhook n'est pas arrivé et t'envoie un e-mail en cas de problème.</p>

<div class="stats">
    <div class="carte stat"><small class="muted">Dernier webhook reçu</small><strong style="font-size:1.3rem">{{ $dernierWebhook ? $dernierWebhook->diffForHumans() : 'Aucun' }}</strong></div>
    <div class="carte stat"><small class="muted">En attente depuis plus de {{ $seuil }} min</small><strong>{{ $nbBloques }}</strong></div>
    <div class="carte stat"><small class="muted">Webhooks reçus (24 h)</small><strong>{{ $compteurs['webhook'] ?? 0 }}</strong></div>
    <div class="carte stat"><small class="muted">Récupérés sans webhook (24 h)</small><strong>{{ $compteurs['reconciliation'] ?? 0 }}</strong></div>
    <div class="carte stat"><small class="muted">Erreurs GeniusPay (24 h)</small><strong>{{ $compteurs['erreur_api'] ?? 0 }}</strong></div>
    <div class="carte stat"><small class="muted">Alertes envoyées (24 h)</small><strong>{{ $compteurs['alerte'] ?? 0 }}</strong></div>
</div>

<div class="carte" style="overflow-x:auto;margin-bottom:1.5rem">
    <h2>Paiements en attente</h2>
    <table class="tableau">
        <thead><tr><th>Créé</th><th>Référence</th><th>Étudiant</th><th>Formule</th><th>Montant</th></tr></thead>
        <tbody>
            @forelse ($enAttente as $p)
                <tr>
                    <td>{{ $p->created_at->diffForHumans() }}</td>
                    <td><small>{{ $p->reference }}</small></td>
                    <td>{{ $p->user->name }}<br><small class="muted">{{ $p->telephone }}</small></td>
                    <td>{{ $p->abonnement->plan->nom }} · {{ $p->abonnement->plan->filiere->nom }}</td>
                    <td>{{ number_format($p->montant, 0, ',', ' ') }} {{ $p->devise }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Aucun paiement en attente.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="carte" style="overflow-x:auto">
    <h2>Journal d'audit</h2>
    <table class="tableau">
        <thead><tr><th>Date</th><th>Type</th><th>Événement</th></tr></thead>
        <tbody>
            @forelse ($journal as $ligne)
                <tr>
                    <td>{{ $ligne->created_at->translatedFormat('d/m H:i:s') }}</td>
                    <td><span class="badge {{ in_array($ligne->type, ['erreur_api', 'alerte'], true) ? 'badge-off' : ($ligne->type === 'reconciliation' ? 'badge-attente' : '') }}">{{ $ligne->type }}</span></td>
                    <td>{{ $ligne->message }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">Le journal est vide.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:1rem">{{ $journal->links() }}</div>
@endsection