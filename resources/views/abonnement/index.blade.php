@extends('layouts.app')

@section('titre', 'Mon abonnement')

@section('contenu')
<div class="conteneur section">
    <h1>Abonnement {{ $filiere->nom }}</h1>

    @if (session('erreur'))
        <div class="alerte" style="margin-bottom:1rem">{{ session('erreur') }}</div>
    @endif

    @if ($abonnement)
        <div class="carte abonnement">
            <span class="badge badge-ok">Abonnement actif</span>
            <p>Formule {{ $abonnement->plan->nom }}, valable jusqu'au {{ $abonnement->fin_le->translatedFormat('d F Y') }}.</p>
            <p class="muted">Si tu prolonges, la nouvelle durée s'ajoute à la fin de ton abonnement actuel.</p>
        </div>
    @endif

    <form class="carte formulaire" method="POST" action="{{ route('abonnement.payer') }}" style="max-width:640px">
        @csrf
        <h2>Choisis ta formule</h2>

        <div class="formules">
            @foreach ($plans as $plan)
                <label class="formule">
                    <input type="radio" name="plan_id" value="{{ $plan->id }}" @checked((int) old('plan_id', $planParDefaut) === $plan->id) required>
                    <span class="formule-nom">{{ $plan->nom }}</span>
                    <span class="formule-prix">{{ number_format($plan->prix, 0, ',', ' ') }} FCFA</span>
                    <small class="muted">{{ $plan->duree_jours }} jours</small>
                    @if ($mensuel && $plan->duree_jours > 30 && ($economie = $mensuel->prix * ($plan->duree_jours / 30) - $plan->prix) > 0)
                        <span class="formule-eco">Tu économises {{ number_format($economie, 0, ',', ' ') }} FCFA</span>
                    @endif
                </label>
            @endforeach
        </div>
        @error('plan_id')<small class="erreur">{{ $message }}</small>@enderror

        <div class="champ">
            <label for="telephone">Numéro Mobile Money</label>
            <input id="telephone" type="tel" name="telephone" value="{{ old('telephone', $user->telephone) }}" required>
            @error('telephone')<small class="erreur">{{ $message }}</small>@enderror
        </div>

        <button type="submit" class="btn btn-primaire btn-bloc">Payer avec Mobile Money</button>
        <p class="lien-bas">Paiement sécurisé par GeniusPay. Tu choisis ton moyen de paiement sur la page suivante.</p>
    </form>

    @if ($paiements->isNotEmpty())
        <h2 class="semestre-titre">Mes derniers paiements</h2>
        <div class="carte" style="overflow-x:auto">
            <table class="tableau">
                <thead>
                    <tr><th>Date</th><th>Formule</th><th>Montant</th><th>Statut</th></tr>
                </thead>
                <tbody>
                    @foreach ($paiements as $p)
                        <tr>
                            <td>{{ $p->created_at->translatedFormat('d/m/Y H:i') }}</td>
                            <td>{{ $p->abonnement->plan->nom }} · {{ $p->abonnement->plan->filiere->nom }}</td>
                            <td>{{ number_format($p->montant, 0, ',', ' ') }} FCFA</td>
                            <td>
                                <span class="badge {{ ['reussi' => 'badge-ok', 'echoue' => 'badge-off', 'en_attente' => 'badge-attente'][$p->statut] ?? '' }}">
                                    {{ ['reussi' => 'Réussi', 'echoue' => 'Échoué', 'en_attente' => 'En attente'][$p->statut] ?? $p->statut }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection