@extends('layouts.admin')

@section('titre', $universite->exists ? 'Modifier l\'université' : 'Nouvelle université')

@section('contenu')
<div class="admin-entete">
    <h1>{{ $universite->exists ? 'Modifier l\'université' : 'Nouvelle université' }}</h1>
    <a class="btn btn-contour btn-sm" href="{{ route('admin.universites.index') }}">← Retour</a>
</div>

<form class="carte formulaire form-large" method="POST"
      action="{{ $universite->exists ? route('admin.universites.update', $universite) : route('admin.universites.store') }}">
    @csrf
    @if ($universite->exists) @method('PUT') @endif

    <div class="champ">
        <label for="nom">Nom complet</label>
        <input id="nom" name="nom" value="{{ old('nom', $universite->nom) }}" required>
        @error('nom')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="ligne-2">
        <div class="champ">
            <label for="sigle">Sigle</label>
            <input id="sigle" name="sigle" value="{{ old('sigle', $universite->sigle) }}">
            @error('sigle')<small class="erreur">{{ $message }}</small>@enderror
        </div>
        <div class="champ">
            <label for="ville">Ville</label>
            <input id="ville" name="ville" value="{{ old('ville', $universite->ville) }}">
            @error('ville')<small class="erreur">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="champ">
        <label for="type">Type</label>
        <select id="type" name="type" required>
            <option value="prive" @selected(old('type', $universite->type) === 'prive')>Privée</option>
            <option value="public" @selected(old('type', $universite->type) === 'public')>Publique</option>
        </select>
        @error('type')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="champ">
        <label for="slug">Lien (facultatif, généré automatiquement)</label>
        <input id="slug" name="slug" value="{{ old('slug', $universite->exists ? $universite->slug : '') }}">
        @error('slug')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="cases">
        <input type="hidden" name="actif" value="0">
        <label class="case"><input type="checkbox" name="actif" value="1" @checked((bool) old('actif', $universite->actif))> Active (proposée à l'inscription)</label>
    </div>

    <button type="submit" class="btn btn-primaire">{{ $universite->exists ? 'Enregistrer' : 'Ajouter' }}</button>
</form>
@endsection