@extends('layouts.admin')

@section('titre', $classe->exists ? 'Modifier le cours en direct' : 'Planifier un cours en direct')

@section('contenu')
<div class="admin-entete">
    <h1>{{ $classe->exists ? 'Modifier le cours en direct' : 'Planifier un cours en direct' }}</h1>
    <a class="btn btn-contour btn-sm" href="{{ route('admin.direct.index') }}">← Retour</a>
</div>

<form class="carte formulaire form-large" method="POST"
      action="{{ $classe->exists ? route('admin.direct.update', $classe) : route('admin.direct.store') }}">
    @csrf
    @if ($classe->exists) @method('PUT') @endif

    <div class="champ">
        <label for="titre">Titre du cours</label>
        <input id="titre" name="titre" value="{{ old('titre', $classe->titre) }}" required>
        @error('titre')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="ligne-2">
        <div class="champ">
            <label for="filiere_id">Filière</label>
            <select id="filiere_id" name="filiere_id" required>
                <option value="">Choisir…</option>
                @foreach ($filieres as $f)
                    <option value="{{ $f->id }}" @selected((int) old('filiere_id', $classe->filiere_id) === $f->id)>{{ $f->nom }}</option>
                @endforeach
            </select>
            @error('filiere_id')<small class="erreur">{{ $message }}</small>@enderror
        </div>
        <div class="champ">
            <label for="matiere_id">Matière (facultatif)</label>
            <select id="matiere_id" name="matiere_id">
                <option value="">Aucune</option>
                @foreach ($matieres as $m)
                    <option value="{{ $m->id }}" data-filiere="{{ $m->filiere_id }}" @selected((int) old('matiere_id', $classe->matiere_id) === $m->id)>
                        {{ $m->niveau }} · {{ $m->nom }}{{ $m->parcours ? ' ('.$m->parcours.')' : '' }}
                    </option>
                @endforeach
            </select>
            @error('matiere_id')<small class="erreur">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="ligne-2">
        <div class="champ">
            <label for="debut_le">Début</label>
            <input id="debut_le" type="datetime-local" name="debut_le"
                   value="{{ old('debut_le', $classe->debut_le?->format('Y-m-d\TH:i')) }}" required>
            @error('debut_le')<small class="erreur">{{ $message }}</small>@enderror
        </div>
        <div class="champ">
            <label for="duree_minutes">Durée (minutes)</label>
            <input id="duree_minutes" type="number" name="duree_minutes" min="15" max="300" step="5"
                   value="{{ old('duree_minutes', $classe->duree_minutes) }}" required>
            @error('duree_minutes')<small class="erreur">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="champ">
        <label for="animateur_id">Animateur</label>
        <select id="animateur_id" name="animateur_id">
            @foreach ($animateurs as $a)
                <option value="{{ $a->id }}" @selected((int) old('animateur_id', $classe->animateur_id) === $a->id)>{{ $a->name }} ({{ $a->role }})</option>
            @endforeach
        </select>
        <small class="muted">L'animateur et les administrateurs sont modérateurs de la salle (couper un micro, retirer quelqu'un).</small>
        @error('animateur_id')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="champ">
        <label for="description">Description (Markdown, facultatif)</label>
        <textarea id="description" name="description" rows="5">{{ old('description', $classe->description) }}</textarea>
        @error('description')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="champ">
        <label for="replay_url">Lien du replay (à remplir après le cours, facultatif)</label>
        <input id="replay_url" type="url" name="replay_url" value="{{ old('replay_url', $classe->replay_url) }}" placeholder="https://www.youtube.com/watch?v=…">
        @error('replay_url')<small class="erreur">{{ $message }}</small>@enderror
    </div>

    <div class="cases">
        <input type="hidden" name="est_gratuit" value="0">
        <label class="case"><input type="checkbox" name="est_gratuit" value="1" @checked((bool) old('est_gratuit', $classe->est_gratuit))> Cours gratuit (ouvert à tout compte connecté)</label>
        @if ($classe->exists)
            <label class="case"><input type="checkbox" name="statut" value="annule" @checked(old('statut', $classe->statut) === 'annule')> Cours annulé</label>
        @endif
    </div>

    <button type="submit" class="btn btn-primaire">{{ $classe->exists ? 'Enregistrer' : 'Planifier le cours' }}</button>
</form>
@endsection

@push('scripts')
<script>
    const filiere = document.getElementById('filiere_id');
    const matiere = document.getElementById('matiere_id');

    function filtrer() {
        Array.from(matiere.options).forEach((option) => {
            if (!option.value) return;
            const masquer = filiere.value !== '' && option.dataset.filiere !== filiere.value;
            option.hidden = masquer;
            option.disabled = masquer;
        });

        const choix = matiere.selectedOptions[0];
        if (choix && choix.disabled) matiere.value = '';
    }

    filiere.addEventListener('change', filtrer);
    filtrer();
</script>
@endpush