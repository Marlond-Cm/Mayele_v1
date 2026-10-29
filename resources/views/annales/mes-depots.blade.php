@extends('layouts.app')

@section('titre', 'Mes dépôts')

@section('contenu')
<div class="conteneur section">
    <div class="admin-entete">
        <h1>Mes dépôts d'annales</h1>
        <a class="btn btn-primaire" href="{{ route('annales.deposer') }}">+ Déposer une annale</a>
    </div>

    @if (session('succes'))
        <div class="alerte alerte-ok" style="margin-bottom:1rem">{{ session('succes') }}</div>
    @endif

    <div class="carte" style="overflow-x:auto">
        <table class="tableau">
            <thead><tr><th>Matière</th><th>Université</th><th>Année</th><th>Fichiers</th><th>Statut</th></tr></thead>
            <tbody>
                @forelse ($depots as $depot)
                    <tr>
                        <td>{{ $depot->matiere->nom }}<br><small class="muted">{{ $depot->matiere->filiere->nom }} · {{ $depot->matiere->niveau }}</small></td>
                        <td>{{ $depot->universite->sigle ?: $depot->universite->nom }}</td>
                        <td>{{ $depot->annee }}<br><small class="muted">{{ $depot->session }} · {{ $depot->type }}</small></td>
                        <td>
                            <a target="_blank" href="{{ route('annales.fichier', [$depot, 'sujet']) }}">Sujet</a>
                            @if ($depot->fichier_corrige)
                                · <a target="_blank" href="{{ route('annales.fichier', [$depot, 'corrige']) }}">Corrigé</a>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ ['valide' => 'badge-ok', 'refuse' => 'badge-off', 'en_attente' => 'badge-attente'][$depot->statut] ?? '' }}">
                                {{ ['valide' => 'Validée', 'refuse' => 'Refusée', 'en_attente' => 'En attente'][$depot->statut] ?? $depot->statut }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Tu n'as encore rien déposé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection