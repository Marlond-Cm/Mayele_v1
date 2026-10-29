@extends('layouts.admin')

@section('titre', $exercice->exists ? 'Modifier l\'exercice' : 'Nouvel exercice')

@section('contenu')
<div class="admin-entete">
    <h1>{{ $exercice->exists ? 'Modifier l\'exercice' : 'Nouvel exercice' }}</h1>
    <a class="btn btn-contour btn-sm" href="{{ route('admin.exercices.index') }}">← Retour</a>
</div>

<form class="carte formulaire form-large" method="POST"
      action="{{ $exercice->exists ? route('admin.exercices.update', $exercice) : route('admin.exercices.store') }}">
    @csrf
    @if ($exercice->exists) @method('PUT') @endif

    <div class="champ">
        <label for="titre">Titre</label>
        <input id="titre" name="titre" value="{{ old('titre', $exercice->titre) }}" required>
        @error('titre')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="ligne-2">
        <div class="champ">
            <label for="langage">Langage</label>
            <select id="langage" name="langage" required>
                @foreach (\App\Models\Exercice::LANGAGES as $cle => $libelle)
                    <option value="{{ $cle }}" @selected(old('langage', $exercice->langage) === $cle)>{{ $libelle }}</option>
                @endforeach
            </select>
            @error('langage')<small class="erreur">{{ $message }}</small>@enderror
        </div>
        <div class="champ">
            <label for="difficulte">Difficulté</label>
            <select id="difficulte" name="difficulte">
                @foreach (\App\Models\Exercice::DIFFICULTES as $cle => $libelle)
                    <option value="{{ $cle }}" @selected(old('difficulte', $exercice->difficulte) === $cle)>{{ $libelle }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="ligne-2">
        <div class="champ">
            <label for="filiere_id">Filière</label>
            <select id="filiere_id" name="filiere_id" required>
                <option value="">Choisir…</option>
                @foreach ($filieres as $f)
                    <option value="{{ $f->id }}" @selected((int) old('filiere_id', $exercice->filiere_id) === $f->id)>{{ $f->nom }}</option>
                @endforeach
            </select>
            @error('filiere_id')<small class="erreur">{{ $message }}</small>@enderror
        </div>
        <div class="champ">
            <label for="ordre">Ordre d'affichage</label>
            <input id="ordre" type="number" name="ordre" min="0" max="999" value="{{ old('ordre', $exercice->ordre) }}">
        </div>
    </div>

    <div class="ligne-2">
        <div class="champ">
            <label for="matiere_id">Matière liée (facultatif)</label>
            <select id="matiere_id" name="matiere_id">
                <option value="">Aucune</option>
                @foreach ($matieres as $m)
                    <option value="{{ $m->id }}" @selected((int) old('matiere_id', $exercice->matiere_id) === $m->id)>{{ $m->niveau }} · {{ $m->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="champ">
            <label for="chapitre_id">Chapitre lié (facultatif)</label>
            <select id="chapitre_id" name="chapitre_id">
                <option value="">Aucun</option>
                @foreach ($chapitres as $nomMatiere => $liste)
                    <optgroup label="{{ $nomMatiere }}">
                        @foreach ($liste as $c)
                            <option value="{{ $c->id }}" @selected((int) old('chapitre_id', $exercice->chapitre_id) === $c->id)>{{ $c->titre }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <small class="muted">L'exercice apparaît alors sous ce chapitre.</small>
        </div>
    </div>

    <div class="champ">
        <label for="enonce">Énoncé (Markdown)</label>
        <textarea id="enonce" name="enonce" rows="8" required>{{ old('enonce', $exercice->enonce) }}</textarea>
        @error('enonce')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="champ">
        <label for="code_depart">Code de départ</label>
        <textarea id="code_depart" name="code_depart" rows="6" style="font-family:monospace">{{ old('code_depart', $exercice->code_depart) }}</textarea>
        @error('code_depart')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="champ">
        <label for="tests_texte">Tests automatiques</label>
        <textarea id="tests_texte" name="tests_texte" rows="14" style="font-family:monospace" required>{{ old('tests_texte', $testsTexte) }}</textarea>
        @foreach ($errors->get('tests_texte') as $erreur)
            <small class="erreur">{{ $erreur }}</small>
        @endforeach
        <small class="muted">Sépare les tests par une ligne <code>---</code>. Ajoute <code>[cache]</code> en première ligne d'un test pour qu'il soit caché à l'étudiant.</small>

        <details style="margin-top:.6rem">
            <summary>Exemples de format</summary>
            <p><strong>Python / JavaScript</strong> (entrée lue avec input() ou lire(), sortie affichée) :</p>
            <div class="exemple">[visible]
entree:
3
5
sortie:
8
---
[cache]
entree:
10
20
sortie:
30</div>
            <p><strong>SQL</strong> (préparation de la base, puis lignes attendues, colonnes séparées par |) :</p>
            <div class="exemple">[visible]
preparation:
CREATE TABLE etudiants (id INTEGER, nom TEXT, note REAL);
INSERT INTO etudiants VALUES (1,'Marie',15),(2,'Paul',9);
attendu:
Marie
ordre: oui</div>
            <p><strong>HTML</strong> (éléments à trouver dans le code de l'étudiant) :</p>
            <div class="exemple">[visible]
selecteur: h1
texte: Bienvenue
min: 1</div>
        </details>
    </div>

    <div class="champ">
        <label for="indice">Indice (facultatif)</label>
        <textarea id="indice" name="indice" rows="2">{{ old('indice', $exercice->indice) }}</textarea>
    </div>

    <div class="champ">
        <label for="solution">Solution (révélée après 2 essais)</label>
        <textarea id="solution" name="solution" rows="6" style="font-family:monospace">{{ old('solution', $exercice->solution) }}</textarea>
    </div>

    <div class="champ">
        <label for="slug">Lien (facultatif, généré automatiquement)</label>
        <input id="slug" name="slug" value="{{ old('slug', $exercice->exists ? $exercice->slug : '') }}">
        @error('slug')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="cases">
        <input type="hidden" name="est_gratuit" value="0">
        <label class="case"><input type="checkbox" name="est_gratuit" value="1" @checked((bool) old('est_gratuit', $exercice->est_gratuit))> Gratuit (aperçu pour tout compte connecté)</label>
        <input type="hidden" name="publie" value="0">
        <label class="case"><input type="checkbox" name="publie" value="1" @checked((bool) old('publie', $exercice->publie))> Publié</label>
    </div>

    <button type="submit" class="btn btn-primaire">{{ $exercice->exists ? 'Enregistrer' : 'Créer l\'exercice' }}</button>
</form>
@endsection