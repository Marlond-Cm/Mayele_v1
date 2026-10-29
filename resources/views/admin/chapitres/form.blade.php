@extends('layouts.admin')

@section('titre', $chapitre->exists ? 'Modifier le chapitre' : 'Nouveau chapitre')

@section('contenu')
<div class="admin-entete">
    <div>
        <p class="fil-ariane"><a href="{{ route('admin.matieres.chapitres.index', $matiere) }}">← {{ $matiere->nom }}</a></p>
        <h1>{{ $chapitre->exists ? 'Modifier le chapitre' : 'Nouveau chapitre' }}</h1>
    </div>
</div>

<form class="carte formulaire form-large" method="POST" enctype="multipart/form-data"
      action="{{ $chapitre->exists ? route('admin.chapitres.update', $chapitre) : route('admin.matieres.chapitres.store', $matiere) }}">
    @csrf
    @if ($chapitre->exists) @method('PUT') @endif

    <div class="champ">
        <label for="titre">Titre du chapitre</label>
        <input id="titre" name="titre" value="{{ old('titre', $chapitre->titre) }}" required>
        @error('titre')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="champ">
        <label for="contenu">Contenu (Markdown)</label>
        <textarea id="contenu" name="contenu" rows="16" placeholder="## Titre de section&#10;&#10;Texte en **gras**, listes avec -, tableaux, code…">{{ old('contenu', $chapitre->contenu) }}</textarea>
        <small class="muted">Titres : ## Titre · Gras : **texte** · Liste : - élément · Lien : [texte](https://…) · Image : ![alt](https://…)</small>
        @error('contenu')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="champ">
        <label for="video_url">Vidéo (facultatif)</label>
        <input id="video_url" type="url" name="video_url" value="{{ old('video_url', $chapitre->video_url) }}" placeholder="https://www.youtube.com/watch?v=… ou lien .mp4">
        <small class="muted">Lien YouTube, ou fichier direct .mp4 / .webm.</small>
        @error('video_url')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="champ">
        <label for="fichier_pdf">Fichier PDF (facultatif, 20 Mo max)</label>
        <input id="fichier_pdf" type="file" name="fichier_pdf" accept="application/pdf">
        @if ($chapitre->fichier_pdf)
            <small class="muted">Un PDF est déjà enregistré. En envoyer un nouveau le remplace.</small>
            <input type="hidden" name="supprimer_pdf" value="0">
            <label class="case"><input type="checkbox" name="supprimer_pdf" value="1"> Supprimer le PDF actuel</label>
        @endif
        @error('fichier_pdf')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="ligne-2">
        <div class="champ">
            <label for="duree_minutes">Durée (minutes)</label>
            <input id="duree_minutes" type="number" name="duree_minutes" min="0" max="600" value="{{ old('duree_minutes', $chapitre->duree_minutes) }}">
            @error('duree_minutes')<small class="erreur">{{ $message }}</small>@enderror
        </div>
        <div class="champ">
            <label for="ordre">Ordre (laisser vide = à la suite)</label>
            <input id="ordre" type="number" name="ordre" min="0" max="999" value="{{ old('ordre', $chapitre->exists ? $chapitre->ordre : '') }}">
            @error('ordre')<small class="erreur">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="champ">
        <label for="slug">Lien (facultatif, généré automatiquement)</label>
        <input id="slug" name="slug" value="{{ old('slug', $chapitre->exists ? $chapitre->slug : '') }}">
        @error('slug')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="cases">
        <input type="hidden" name="est_gratuit" value="0">
        <label class="case"><input type="checkbox" name="est_gratuit" value="1" @checked((bool) old('est_gratuit', $chapitre->est_gratuit))> Chapitre gratuit (aperçu pour tous)</label>
        <input type="hidden" name="publie" value="0">
        <label class="case"><input type="checkbox" name="publie" value="1" @checked((bool) old('publie', $chapitre->publie))> Publié</label>
    </div>

    <button type="submit" class="btn btn-primaire">{{ $chapitre->exists ? 'Enregistrer' : 'Ajouter le chapitre' }}</button>
</form>
@endsection