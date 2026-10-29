@extends('layouts.admin')

@section('titre', 'Annales')

@section('contenu')
<div class="admin-entete">
    <h1>Annales</h1>
    <a class="btn btn-primaire" href="{{ route('admin.annales.create') }}">+ Ajouter une annale</a>
</div>

<form class="filtres" method="GET">
    <select name="statut">
        <option value="">Tous les statuts</option>
        <option value="en_attente" @selected(request('statut') === 'en_attente')>En attente</option>
        <option value="valide" @selected(request('statut') === 'valide')>Validées</option>
        <option value="refuse" @selected(request('statut') === 'refuse')>Refusées</option>
    </select>
    <select name="universite">
        <option value="">Toutes les universités</option>
        @foreach ($universites as $universite)
            <option value="{{ $universite->id }}" @selected(request('universite') == $universite->id)>{{ $universite->nom }}</option>
        @endforeach
    </select>
    <button class="btn btn-contour btn-sm" type="submit">Filtrer</button>
</form>

<div class="carte" style="overflow-x:auto">
    <table class="tableau">
        <thead><tr><th>Matière</th><th>Université</th><th>Année</th><th>Type</th><th>Fichiers</th><th>Statut</th><th></th></tr></thead>
        <tbody>
            @forelse ($annales as $annale)
                <tr>
                    <td>{{ $annale->matiere->nom }}<br><small class="muted">{{ $annale->matiere->filiere->nom }} · {{ $annale->matiere->niveau }}</small></td>
                    <td>{{ $annale->universite->sigle ?? $annale->universite->nom }}</td>
                    <td>{{ $annale->annee }}<br><small class="muted">{{ $annale->session }}</small></td>
                    <td>{{ $annale->type }}</td>
                    <td>
                        <a href="{{ route('admin.annales.fichier', [$annale, 'sujet']) }}" target="_blank">Sujet</a>
                        @if ($annale->fichier_corrige)
                            · <a href="{{ route('admin.annales.fichier', [$annale, 'corrige']) }}" target="_blank">Corrigé</a>
                        @endif
                    </td>
                    <td><span class="badge {{ ['valide' => 'badge-ok', 'refuse' => 'badge-off', 'en_attente' => 'badge-attente'][$annale->statut] ?? '' }}">{{ $annale->statut }}</span></td>
                    <td>
                        <div class="actions-ligne">
                            @if ($annale->statut !== 'valide')
                                <form method="POST" action="{{ route('admin.annales.valider', $annale) }}">@csrf<button class="btn btn-primaire btn-sm" type="submit">Valider</button></form>
                            @endif
                            @if ($annale->statut !== 'refuse')
                                <form method="POST" action="{{ route('admin.annales.refuser', $annale) }}">@csrf<button class="btn btn-contour btn-sm" type="submit">Refuser</button></form>
                            @endif
                            <a class="btn btn-contour btn-sm" href="{{ route('admin.annales.edit', $annale) }}">Modifier</a>
                            <form method="POST" action="{{ route('admin.annales.destroy', $annale) }}" onsubmit="return confirm('Supprimer cette annale ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Aucune annale.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:1rem">{{ $annales->links() }}</div>
@endsection