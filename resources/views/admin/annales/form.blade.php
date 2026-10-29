@extends('layouts.admin')

@section('titre', $annale->exists ? 'Modifier l\'annale' : 'Ajouter une annale')

@section('contenu')
<div class="admin-entete">
    <h1>{{ $annale->exists ? 'Modifier l\'annale' : 'Ajouter une annale' }}</h1>
    <a class="btn btn-contour btn-sm" href="{{ route('admin.annales.index') }}">← Retour</a>
</div>

<form class="carte formulaire form-large" method="POST" enctype="multipart/form-data"
      action="{{ $annale->exists ? route('admin.annales.update', $annale) : route('admin.annales.store') }}">
    @csrf
    @if ($annale->exists) @method('PUT') @endif

    <div class="champ">
        <label for="matiere_id">Matière</label>
        <select id="matiere_id" name="matiere_id" required>
            <option value="">Choisir…</option>
            @foreach ($matieres as $nomFiliere => $liste)
                <optgroup label="{{ $nomFiliere }}">
                    @foreach ($liste as $m)
                        <option value="{{ $m->id }}" @selected((int) old('matiere_id', $annale->matiere_id) === $m->id)>
                            {{ $m->niveau }} · {{ $m->nom }}{{ $m->parcours ? ' ('.$m->parcours.')' : '' }}
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('matiere_id')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="champ">
        <label for="universite_id">Université</label>
        <select id="universite_id" name="universite_id" required>
            <option value="">Choisir…</option>
            @foreach ($universites as $universite)
                <option value="{{ $universite->id }}" @selected((int) old('universite_id', $annale->universite_id) === $universite->id)>{{ $universite->nom }}</option>
            @endforeach
        </select>
        @error('universite_id')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="ligne-2">
        <div class="champ">
            <label for="annee">Année</label>
            <input id="annee" type="number" name="annee" value="{{ old('annee', $annale->annee) }}" required>
            @error('annee')<small class="erreur">{{ $message }}</small>@enderror
        </div>
        <div class="champ">
            <label for="session">Session</label>
            <select id="session" name="session">
                <option value="normale" @selected(old('session', $annale->session) === 'normale')>Normale</option>
                <option value="rattrapage" @selected(old('session', $annale->session) === 'rattrapage')>Rattrapage</option>
            </select>
        </div>
    </div>

    <div class="ligne-2">
        <div class="champ">
            <label for="type">Type</label>
            <select id="type" name="type">
                <option value="examen" @selected(old('type', $annale->type) === 'examen')>Examen</option>
                <option value="partiel" @selected(old('type', $annale->type) === 'partiel')>Partiel</option>
                <option value="td" @selected(old('type', $annale->type) === 'td')>TD</option>
            </select>
        </div>
        <div class="champ">
            <label for="statut">Statut</label>
            <select id="statut" name="statut">
                <option value="valide" @selected(old('statut', $annale->statut) === 'valide')>Validée</option>
                <option value="en_attente" @selected(old('statut', $annale->statut) === 'en_attente')>En attente</option>
                <option value="refuse" @selected(old('statut', $annale->statut) === 'refuse')>Refusée</option>
            </select>
        </div>
    </div>

    <div class="champ">
        <label for="fichier_sujet">Sujet (PDF){{ $annale->exists ? ' : laisser vide pour conserver' : '' }}</label>
        <input id="fichier_sujet" type="file" name="fichier_sujet" accept="application/pdf" @required(! $annale->exists)>
        @error('fichier_sujet')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="champ">
        <label for="fichier_corrige">Corrigé (PDF, facultatif)</label>
        <input id="fichier_corrige" type="file" name="fichier_corrige" accept="application/pdf">
        @error('fichier_corrige')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <button type="submit" class="btn btn-primaire">{{ $annale->exists ? 'Enregistrer' : 'Ajouter l\'annale' }}</button>
</form>
@endsection