@extends('layouts.app')

@section('titre', 'Paiement')

@section('contenu')
@php($plan = $paiement->abonnement->plan)
<div class="conteneur auth-wrap">
    <div class="carte resultat">
        @if ($paiement->statut === 'reussi')
            <div class="icone">✅</div>
            <h1>Paiement réussi</h1>
            <p>Ton abonnement {{ $plan->nom }} {{ $plan->filiere->nom }} est actif jusqu'au
                <strong>{{ $paiement->abonnement->fin_le->translatedFormat('d F Y') }}</strong>.</p>
            <a class="btn btn-primaire" href="{{ route('filieres.show', $plan->filiere->slug) }}">Commencer à apprendre</a>
        @elseif ($paiement->statut === 'echoue')
            <div class="icone">❌</div>
            <h1>Paiement non abouti</h1>
            <p class="muted">Le paiement a échoué ou a été annulé. Aucun montant ne t'a été débité. Tu peux réessayer.</p>
            <a class="btn btn-primaire" href="{{ route('abonnement.index', ['filiere' => $plan->filiere->slug]) }}">Réessayer</a>
        @else
            <div class="icone">⏳</div>
            <h1>Confirmation en cours…</h1>
            <p class="muted">Valide le paiement sur ton téléphone si ce n'est pas déjà fait. Cette page se met à jour automatiquement.</p>
        @endif

        <p class="muted" style="margin-top:1.5rem"><small>Référence : {{ $paiement->reference }}</small></p>
    </div>
</div>
@endsection

@if ($paiement->statut === 'en_attente')
    @push('scripts')
    <script>setTimeout(() => window.location.reload(), 6000);</script>
    @endpush
@endif