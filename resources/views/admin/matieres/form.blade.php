@extends('layouts.admin')

@section('titre', $matiere->exists ? 'Modifier la matière' : 'Nouvelle matière')

@section('contenu')
<div class="admin-entete">
    <h1>{{ $matiere->exists ? 'Modifier la matière' : 'Nouvelle matière' }}</h1>
    <a class="btn btn-contour btn-sm" href="{{ route('admin.matieres.index') }}">← Retour</a>
</div>

<form class="carte formulaire form-large" method="POST"
      action="{{ $matiere->exists ? route('admin.matieres.update', $matiere) : route('admin.matieres.store') }}">
    @csrf
    @if ($matiere->exists) @method('PUT') @endif

    <div class="ligne-2">
        <div class="champ">
            <label for="filiere_id">Filière</label>
            <select id="filiere_id" name="filiere_id" required>
                <option value="">Choisir…</option>
                @foreach ($filieres as $filiere)
                    <option value="{{ $filiere->id }}" @selected((int) old('filiere_id', $matiere->filiere_id) === $filiere->id)>{{ $filiere->nom }}</option>
                @endforeach
            </select>
            @error('filiere_id')<small class="erreur">{{ $message }}</small>@enderror
        </div>
        <div class="champ">
            <label for="niveau">Niveau</label>
            <select id="niveau" name="niveau" required>
                @foreach (['L1', 'L2', 'L3'] as $niveau)
                    <option value="{{ $niveau }}" @selected(old('niveau', $matiere->niveau) === $niveau)>{{ $niveau }}</option>
                @endforeach
            </select>
            @error('niveau')<small class="erreur">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="ligne-2">
        <div class="champ">
            <label for="semestre">Semestre</label>
            <select id="semestre" name="semestre" required>
                @foreach (range(1, 6) as $s)
                    <option value="{{ $s }}" @selected((int) old('semestre', $matiere->semestre) === $s)>Semestre {{ $s }}</option>
                @endforeach
            </select>
            @error('semestre')<small class="erreur">{{ $message }}</small>@enderror
        </div>
        <div class="champ">
            <label for="ordre">Ordre d'affichage</label>
            <input id="ordre" type="number" name="ordre" min="0" max="999" value="{{ old('ordre', $matiere->ordre) }}">
            @error('ordre')<small class="erreur">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="champ">
        <label for="nom">Nom de la matière</label>
        <input id="nom" name="nom" value="{{ old('nom', $matiere->nom) }}" required>
        @error('nom')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="champ">
        <label for="parcours">Parcours (L3 uniquement, facultatif)</label>
        <input id="parcours" name="parcours" value="{{ old('parcours', $matiere->parcours) }}" placeholder="Ex. Comptabilité-Finance">
        @error('parcours')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="champ">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3">{{ old('description', $matiere->description) }}</textarea>
        @error('description')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="champ">
        <label for="slug">Lien (facultatif, généré automatiquement)</label>
        <input id="slug" name="slug" value="{{ old('slug', $matiere->exists ? $matiere->slug : '') }}">
        @error('slug')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="cases">
        <input type="hidden" name="publie" value="0">
        <label class="case"><input type="checkbox" name="publie" value="1" @checked((bool) old('publie', $matiere->publie))> Publiée (visible sur le site)</label>
    </div>

    <button type="submit" class="btn btn-primaire">{{ $matiere->exists ? 'Enregistrer' : 'Créer la matière' }}</button>
</form>
@endsection