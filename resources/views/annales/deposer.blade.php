@extends('layouts.app')

@section('titre', 'Déposer une annale')

@section('contenu')
<div class="conteneur auth-wrap" style="max-width:640px">
    <form class="carte formulaire" method="POST" action="{{ route('annales.stocker') }}" enctype="multipart/form-data">
        @csrf
        <h1>Déposer une annale</h1>
        <p class="muted">Dépose uniquement des sujets d'examens déjà passés, en PDF lisible (10 Mo max). Chaque annale validée par l'équipe t'offre <strong>{{ config('mayele.annale_jours') }} jours</strong> d'abonnement.</p>

        @if (session('erreur'))
            <div class="alerte" style="margin-bottom:1rem">{{ session('erreur') }}</div>
        @endif

        <div class="champ">
            <label for="matiere_id">Matière</label>
            <select id="matiere_id" name="matiere_id" required>
                <option value="">Choisir…</option>
                @foreach ($matieres as $nomFiliere => $liste)
                    <optgroup label="{{ $nomFiliere }}">
                        @foreach ($liste as $m)
                            <option value="{{ $m->id }}" @selected((int) old('matiere_id', $matiereChoisie) === $m->id)>
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
                @foreach ($universites as $u)
                    <option value="{{ $u->id }}" @selected((int) old('universite_id', $user->universite_id) === $u->id)>{{ $u->nom }}</option>
                @endforeach
            </select>
            @error('universite_id')<small class="erreur">{{ $message }}</small>@enderror
        </div>

        <div class="ligne-2">
            <div class="champ">
                <label for="annee">Année de l'examen</label>
                <input id="annee" type="number" name="annee" min="2000" max="{{ date('Y') }}" value="{{ old('annee', date('Y')) }}" required>
                @error('annee')<small class="erreur">{{ $message }}</small>@enderror
            </div>
            <div class="champ">
                <label for="session">Session</label>
                <select id="session" name="session">
                    <option value="normale" @selected(old('session') === 'normale')>Normale</option>
                    <option value="rattrapage" @selected(old('session') === 'rattrapage')>Rattrapage</option>
                </select>
            </div>
        </div>

        <div class="champ">
            <label for="type">Type de document</label>
            <select id="type" name="type">
                <option value="examen" @selected(old('type') === 'examen')>Examen</option>
                <option value="partiel" @selected(old('type') === 'partiel')>Partiel</option>
                <option value="td" @selected(old('type') === 'td')>TD</option>
            </select>
        </div>

        <div class="champ">
            <label for="fichier_sujet">Sujet (PDF)</label>
            <input id="fichier_sujet" type="file" name="fichier_sujet" accept="application/pdf" required>
            @error('fichier_sujet')<small class="erreur">{{ $message }}</small>@enderror
        </div>

        <div class="champ">
            <label for="fichier_corrige">Corrigé (PDF, facultatif)</label>
            <input id="fichier_corrige" type="file" name="fichier_corrige" accept="application/pdf">
            @error('fichier_corrige')<small class="erreur">{{ $message }}</small>@enderror
        </div>

        <button type="submit" class="btn btn-primaire btn-bloc">Envoyer pour validation</button>
        <p class="lien-bas"><a href="{{ route('annales.mes-depots') }}">Voir mes dépôts</a></p>
    </form>
</div>
@endsection