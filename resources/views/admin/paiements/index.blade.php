@extends('layouts.admin')

@section('titre', 'Paiements')

@section('contenu')
<div class="admin-entete"><h1>Paiements</h1></div>

<form class="filtres" method="GET">
    <select name="statut">
        <option value="">Tous les statuts</option>
        <option value="reussi" @selected(request('statut') === 'reussi')>Réussis</option>
        <option value="en_attente" @selected(request('statut') === 'en_attente')>En attente</option>
        <option value="echoue" @selected(request('statut') === 'echoue')>Échoués</option>
    </select>
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Référence, nom, e-mail, téléphone">
    <button class="btn btn-contour btn-sm" type="submit">Filtrer</button>
</form>

<div class="carte" style="overflow-x:auto">
    <table class="tableau">
        <thead><tr><th>Date</th><th>Référence</th><th>Étudiant</th><th>Formule</th><th>Montant</th><th>Statut</th><th></th></tr></thead>
        <tbody>
            @forelse ($paiements as $p)
                <tr>
                    <td>{{ $p->created_at->translatedFormat('d/m/Y H:i') }}</td>
                    <td><small>{{ $p->reference }}</small></td>
                    <td><a href="{{ route('admin.utilisateurs.show', $p->user) }}">{{ $p->user->name }}</a><br><small class="muted">{{ $p->telephone }}</small></td>
                    <td>{{ $p->abonnement->plan->nom }} · {{ $p->abonnement->plan->filiere->nom }}</td>
                    <td>{{ number_format($p->montant, 0, ',', ' ') }} {{ $p->devise }}</td>
                    <td><span class="badge {{ ['reussi' => 'badge-ok', 'echoue' => 'badge-off', 'en_attente' => 'badge-attente'][$p->statut] ?? '' }}">{{ $p->statut }}</span></td>
                    <td>
                        @if ($p->statut === 'en_attente' && $p->reference_fournisseur)
                            <form method="POST" action="{{ route('admin.paiements.verifier', $p) }}">
                                @csrf
                                <button class="btn btn-contour btn-sm" type="submit">Vérifier</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Aucun paiement.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:1rem">{{ $paiements->links() }}</div>
@endsection