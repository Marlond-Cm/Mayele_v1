@extends('layouts.admin')

@section('titre', 'Chapitres · '.$matiere->nom)

@section('contenu')
<div class="admin-entete">
    <div>
        <p class="fil-ariane"><a href="{{ route('admin.matieres.index') }}">Matières</a> › {{ $matiere->filiere->nom }} · {{ $matiere->niveau }}</p>
        <h1>{{ $matiere->nom }}</h1>
    </div>
    <a class="btn btn-primaire" href="{{ route('admin.matieres.chapitres.create', $matiere) }}">+ Nouveau chapitre</a>
</div>

<div class="carte" style="overflow-x:auto">
    <table class="tableau">
        <thead>
            <tr><th>#</th><th>Titre</th><th>Accès</th><th>Durée</th><th>Médias</th><th>Statut</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($chapitres as $chapitre)
                <tr>
                    <td>{{ $chapitre->ordre }}</td>
                    <td>{{ $chapitre->titre }}</td>
                    <td><span class="badge {{ $chapitre->est_gratuit ? 'badge-ok' : '' }}">{{ $chapitre->est_gratuit ? 'Gratuit' : 'Abonnés' }}</span></td>
                    <td>{{ $chapitre->duree_minutes }} min</td>
                    <td>{{ $chapitre->video_url ? '🎬' : '' }} {{ $chapitre->fichier_pdf ? '📄' : '' }}</td>
                    <td><span class="badge {{ $chapitre->publie ? 'badge-ok' : 'badge-attente' }}">{{ $chapitre->publie ? 'Publié' : 'Brouillon' }}</span></td>
                    <td>
                        <div class="actions-ligne">
                            @if ($chapitre->publie && $matiere->publie)
                                <a class="btn btn-contour btn-sm" target="_blank" href="{{ route('chapitres.show', [$matiere->slug, $chapitre->slug]) }}">Voir</a>
                            @endif
                            <a class="btn btn-contour btn-sm" href="{{ route('admin.chapitres.edit', $chapitre) }}">Modifier</a>
                            <form method="POST" action="{{ route('admin.chapitres.destroy', $chapitre) }}" onsubmit="return confirm('Supprimer ce chapitre ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Aucun chapitre. Clique sur « Nouveau chapitre » pour commencer.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if (! $matiere->publie)
    <p class="muted" style="margin-top:1rem">Cette matière est masquée : elle n'apparaît pas sur le site tant que tu ne l'as pas publiée (bouton « Modifier » dans la liste des matières).</p>
@endif
@endsection