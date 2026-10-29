@extends('layouts.admin')

@section('titre', 'Tarifs')

@section('contenu')
<div class="admin-entete"><h1>Tarifs des abonnements</h1></div>
<p class="muted">Un changement de prix s'applique aux nouveaux paiements. Les abonnements déjà payés ne changent pas.</p>

<div class="grille">
    @foreach ($filieres as $filiere)
        <div class="carte-filiere" data-theme="{{ $filiere->theme }}">
            <h3>{{ $filiere->nom }}</h3>
            @foreach ($filiere->plans as $plan)
                <form class="ligne-plan" method="POST" action="{{ route('admin.plans.update', $plan) }}">
                    @csrf @method('PUT')
                    <span>{{ $plan->nom }}<br><small class="muted">{{ $plan->duree_jours }} jours</small></span>
                    <input type="number" name="prix" min="0" max="10000000" value="{{ $plan->prix }}" required>
                    <input type="hidden" name="actif" value="0">
                    <label class="case"><input type="checkbox" name="actif" value="1" @checked($plan->actif)> Actif</label>
                    <button class="btn btn-primaire btn-sm" type="submit">OK</button>
                </form>
            @endforeach
        </div>
    @endforeach
</div>
@endsection