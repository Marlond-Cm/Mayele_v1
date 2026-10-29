@extends('layouts.admin')

@section('titre', $utilisateur->name)

@section('contenu')
<div class="admin-entete">
    <div>
        <p class="fil-ariane"><a href="{{ route('admin.utilisateurs.index') }}">← Utilisateurs</a></p>
        <h1>{{ $utilisateur->name }}</h1>
    </div>
</div>

<div class="grille" style="grid-template-columns: 1fr 1fr">
    <div class="carte">
        <h2>Profil</h2>
        <ul class="prix">
            <li><span>E-mail</span><strong>{{ $utilisateur->email }}</strong></li>
            <li><span>Téléphone</span><strong>{{ $utilisateur->telephone }}</strong></li>
            <li><span>Université</span><strong>{{ $utilisateur->universite?->nom }}</strong></li>
            <li><span>Filière</span><strong>{{ $utilisateur->filiere?->nom }} {{ $utilisateur->niveau }}</strong></li>
            <li><span>Inscrit le</span><strong>{{ $utilisateur->created_at->translatedFormat('d F Y') }}</strong></li>
            <li><span>Code de parrainage</span><strong>{{ $utilisateur->code_parrainage }}</strong></li>
            <li><span>Filleuls</span><strong>{{ $filleuls }}</strong></li>
            @if ($utilisateur->parrain)
                <li><span>Parrainé par</span><strong>{{ $utilisateur->parrain->name }}</strong></li>
            @endif
        </ul>
    </div>

    <div class="carte formulaire">
        <h2>Offrir un abonnement</h2>
        <p class="muted">Paiement en espèces, geste commercial ou test : l'abonnement est activé tout de suite (montant 0).</p>
        <form method="POST" action="{{ route('admin.utilisateurs.offrir', $utilisateur) }}">
            @csrf
            <div class="champ">
                <select name="plan_id" required>
                    <option value="">Choisir une formule…</option>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->filiere->nom }} · {{ $plan->nom }}</option>
                    @endforeach
                </select>
                @error('plan_id')<small class="erreur">{{ $message }}</small>@enderror
            </div>
            <button class="btn btn-primaire" type="submit" onclick="return confirm('Activer cet abonnement gratuitement ?')">Activer</button>
        </form>
    </div>
</div>

<h2 class="semestre-titre">Abonnements</h2>
<div class="carte" style="overflow-x:auto">
    <table class="tableau">
        <thead><tr><th>Formule</th><th>Début</th><th>Fin</th><th>Montant</th><th>Statut</th></tr></thead>
        <tbody>
            @forelse ($abonnements as $a)
                <tr>
                    <td>{{ $a->plan->nom }} · {{ $a->plan->filiere->nom }}</td>
                    <td>{{ $a->debut_le?->translatedFormat('d/m/Y') }}</td>
                    <td>{{ $a->fin_le?->translatedFormat('d/m/Y') }}</td>
                    <td>{{ number_format($a->montant, 0, ',', ' ') }}</td>
                    <td><span class="badge {{ $a->estActif() ? 'badge-ok' : 'badge-attente' }}">{{ $a->estActif() ? 'Actif' : $a->statut }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Aucun abonnement.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<h2 class="semestre-titre">Paiements</h2>
<div class="carte" style="overflow-x:auto">
    <table class="tableau">
        <thead><tr><th>Date</th><th>Référence</th><th>Montant</th><th>Statut</th></tr></thead>
        <tbody>
            @forelse ($paiements as $p)
                <tr>
                    <td>{{ $p->created_at->translatedFormat('d/m/Y H:i') }}</td>
                    <td><small>{{ $p->reference }}</small></td>
                    <td>{{ number_format($p->montant, 0, ',', ' ') }} {{ $p->devise }}</td>
                    <td><span class="badge {{ ['reussi' => 'badge-ok', 'echoue' => 'badge-off', 'en_attente' => 'badge-attente'][$p->statut] ?? '' }}">{{ $p->statut }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">Aucun paiement.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection