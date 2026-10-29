@extends('layouts.admin')

@section('titre', 'Tableau de bord')

@section('contenu')
<div class="admin-entete"><h1>Tableau de bord</h1></div>

<div class="stats">
    <div class="carte stat">
        <small class="muted">Étudiants inscrits</small>
        <strong>{{ number_format($stats['etudiants'], 0, ',', ' ') }}</strong>
        <small>+{{ $stats['nouveaux'] }} ce mois</small>
    </div>
    <div class="carte stat">
        <small class="muted">Abonnés actifs</small>
        <strong>{{ number_format($stats['abonnes'], 0, ',', ' ') }}</strong>
    </div>
    <div class="carte stat">
        <small class="muted">Revenus du mois (FCFA)</small>
        <strong>{{ number_format($stats['revenu_mois'], 0, ',', ' ') }}</strong>
    </div>
    <div class="carte stat">
        <small class="muted">Revenus totaux (FCFA)</small>
        <strong>{{ number_format($stats['revenu_total'], 0, ',', ' ') }}</strong>
    </div>
    <a class="carte stat lien-carte" href="{{ route('admin.annales.index', ['statut' => 'en_attente']) }}">
        <small class="muted">Annales à valider</small>
        <strong>{{ $stats['annales_attente'] }}</strong>
    </a>
</div>

<div class="grille" style="grid-template-columns: 1fr 2fr">
    <div class="carte">
        <h2>Étudiants par filière</h2>
        <ul class="prix">
            @foreach ($filieres as $filiere)
                <li><span>{{ $filiere->nom }}</span><strong>{{ $filiere->etudiants_count }}</strong></li>
            @endforeach
        </ul>
    </div>

    <div class="carte" style="overflow-x:auto">
        <h2>Derniers paiements</h2>
        <table class="tableau">
            <thead><tr><th>Date</th><th>Étudiant</th><th>Formule</th><th>Montant</th><th>Statut</th></tr></thead>
            <tbody>
                @forelse ($derniersPaiements as $p)
                    <tr>
                        <td>{{ $p->created_at->translatedFormat('d/m H:i') }}</td>
                        <td>{{ $p->user->name }}</td>
                        <td>{{ $p->abonnement->plan->nom }} · {{ $p->abonnement->plan->filiere->nom }}</td>
                        <td>{{ number_format($p->montant, 0, ',', ' ') }}</td>
                        <td><span class="badge {{ ['reussi' => 'badge-ok', 'echoue' => 'badge-off', 'en_attente' => 'badge-attente'][$p->statut] ?? '' }}">{{ $p->statut }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Aucun paiement pour le moment.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection