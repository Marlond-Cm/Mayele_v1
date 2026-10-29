@extends('layouts.admin')

@section('titre', $quiz->exists ? 'Modifier le quiz' : 'Nouveau quiz')

@section('contenu')
<div class="admin-entete">
    <h1>{{ $quiz->exists ? 'Modifier le quiz' : 'Nouveau quiz' }}</h1>
    <a class="btn btn-contour btn-sm" href="{{ route('admin.quiz.index') }}">← Retour</a>
</div>

<form id="form-quiz" class="carte formulaire form-large" method="POST"
      action="{{ $quiz->exists ? route('admin.quiz.update', $quiz) : route('admin.quiz.store') }}">
    @csrf
    @if ($quiz->exists) @method('PUT') @endif

    <div class="champ">
        <label for="matiere_id">Matière</label>
        <select id="matiere_id" name="matiere_id" required>
            <option value="">Choisir…</option>
            @foreach ($matieres as $nomFiliere => $liste)
                <optgroup label="{{ $nomFiliere }}">
                    @foreach ($liste as $m)
                        <option value="{{ $m->id }}" @selected((int) old('matiere_id', $quiz->matiere_id) === $m->id)>
                            {{ $m->niveau }} · {{ $m->nom }}{{ $m->parcours ? ' ('.$m->parcours.')' : '' }}
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('matiere_id')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="champ">
        <label for="titre">Titre du quiz</label>
        <input id="titre" name="titre" value="{{ old('titre', $quiz->titre) }}" required>
        @error('titre')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="champ">
        <label for="description">Description (facultatif)</label>
        <textarea id="description" name="description" rows="2">{{ old('description', $quiz->description) }}</textarea>
        @error('description')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="champ">
        <label for="points_par_question">Points par bonne réponse</label>
        <input id="points_par_question" type="number" name="points_par_question" min="1" max="100" value="{{ old('points_par_question', $quiz->points_par_question) }}" required>
        @error('points_par_question')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="generation">
        <strong>✨ Générer un brouillon avec l'IA</strong>
        <p class="muted" style="margin:.3rem 0 .8rem"><small>Choisis la matière ci-dessus, décris le sujet, puis relis et corrige les questions avant d'enregistrer. L'IA peut se tromper.</small></p>
        <div class="champ">
            <input id="gen-sujet" placeholder="Sujet à couvrir (ex. bilan et compte de résultat)">
        </div>
        <div class="ligne-2">
            <div class="champ">
                <label for="gen-nombre">Nombre de questions</label>
                <input id="gen-nombre" type="number" min="3" max="20" value="10">
            </div>
            <label class="case"><input type="checkbox" id="gen-chapitres"> S'appuyer sur les chapitres publiés de la matière</label>
        </div>
        <button type="button" class="btn btn-contour btn-sm" id="btn-generer" data-url="{{ route('admin.quiz.generer') }}">Générer</button>
        <small id="etat-generation" class="muted" style="margin-left:.6rem"></small>
    </div>

    <div class="champ">
        <label for="questions">Questions</label>
        <textarea id="questions" name="questions" rows="18" required>{{ old('questions', $texte) }}</textarea>
        @foreach ($errors->get('questions') as $erreur)
            <small class="erreur">{{ $erreur }}</small>
        @endforeach

        <p class="muted" style="margin:.8rem 0 .3rem"><small>Une question par bloc, séparée de la suivante par une ligne vide. <strong>=</strong> bonne réponse (une seule), <strong>-</strong> mauvaise réponse, <strong>&gt;</strong> explication (facultative). L'énoncé ne doit pas commencer par = - ou &gt;.</small></p>
        <div class="exemple">Quelle est la capitale du Congo-Brazzaville ?
= Brazzaville
- Pointe-Noire
- Dolisie
&gt; Brazzaville est la capitale politique, Pointe-Noire la capitale économique.

Combien font 2 + 2 ?
- 3
= 4
- 5</div>
        @if ($quiz->exists)
            <p class="muted"><small>Si tu modifies les questions, les points déjà gagnés sont conservés, mais la correction détaillée des anciennes tentatives n'est plus exacte.</small></p>
        @endif
    </div>

    <div class="cases">
        <input type="hidden" name="est_gratuit" value="0">
        <label class="case"><input type="checkbox" name="est_gratuit" value="1" @checked((bool) old('est_gratuit', $quiz->est_gratuit))> Quiz gratuit (accessible sans abonnement)</label>
        <input type="hidden" name="publie" value="0">
        <label class="case"><input type="checkbox" name="publie" value="1" @checked((bool) old('publie', $quiz->publie))> Publié</label>
    </div>

    <button type="submit" class="btn btn-primaire">{{ $quiz->exists ? 'Enregistrer' : 'Créer le quiz' }}</button>
</form>
@endsection

@push('scripts')
<script>
    document.getElementById('btn-generer').addEventListener('click', async (e) => {
        const bouton = e.currentTarget;
        const etat = document.getElementById('etat-generation');
        const zone = document.getElementById('questions');
        const matiere = document.getElementById('matiere_id').value;

        if (!matiere) {
            etat.textContent = 'Choisis d\'abord la matière.';
            return;
        }

        if (zone.value.trim() !== '' && !confirm('Remplacer les questions actuelles par celles générées ?')) {
            return;
        }

        const donnees = new FormData();
        donnees.append('_token', document.querySelector('#form-quiz input[name="_token"]').value);
        donnees.append('matiere_id', matiere);
        donnees.append('sujet', document.getElementById('gen-sujet').value);
        donnees.append('nombre', document.getElementById('gen-nombre').value);
        if (document.getElementById('gen-chapitres').checked) {
            donnees.append('utiliser_chapitres', '1');
        }

        bouton.disabled = true;
        etat.textContent = 'Génération en cours (10 à 40 secondes)…';

        try {
            const reponse = await fetch(bouton.dataset.url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: donnees,
            });
            const data = await reponse.json().catch(() => ({}));

            if (!reponse.ok) {
                etat.textContent = data.errors ? Object.values(data.errors).flat()[0] : (data.message || 'Erreur de génération.');
                return;
            }

            zone.value = data.questions;
            etat.textContent = data.avertissements.length
                ? 'Brouillon généré, à corriger : ' + data.avertissements.join(' · ')
                : 'Brouillon généré. Relis-le et corrige-le avant d\'enregistrer.';
        } catch (err) {
            etat.textContent = 'Connexion impossible.';
        } finally {
            bouton.disabled = false;
        }
    });
</script>
@endpush