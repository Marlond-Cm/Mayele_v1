@extends('layouts.app')

@section('titre', 'Inscription')

@section('contenu')
@php($filiereChoisie = old('filiere_id', request('filiere')))
<div class="auth-split">
    <aside class="auth-visuel">
        <h2>Rejoins {{ config('app.name') }}</h2>
        <p>La plateforme pour réussir ta licence.</p>
        <ul class="auth-liste">
            <li>✓ Des cours alignés sur ton programme</li>
            <li>✓ Des chapitres gratuits pour essayer</li>
            <li>✓ Annales, quiz, coach IA et cours en direct</li>
            <li>✓ Paiement simple par Mobile Money</li>
        </ul>
    </aside>

    <section class="auth-form">
        <form class="formulaire" method="POST" action="{{ route('inscription') }}">
            @csrf
            <h1>Créer mon compte</h1>

            <div class="champ">
                <label for="filiere_id">Ma filière</label>
                <select id="filiere_id" name="filiere_id" required>
                    <option value="">Choisir…</option>
                    @foreach ($filieres as $filiere)
                        <option value="{{ $filiere->id }}" data-theme="{{ $filiere->theme }}" @selected($filiereChoisie == $filiere->id)>{{ $filiere->nom }}</option>
                    @endforeach
                </select>
                @error('filiere_id')<small class="erreur">{{ $message }}</small>@enderror
            </div>

            <div class="ligne-2">
                <div class="champ">
                    <label for="universite_id">Mon université</label>
                    <select id="universite_id" name="universite_id" required>
                        <option value="">Choisir…</option>
                        @foreach ($universites as $universite)
                            <option value="{{ $universite->id }}" @selected(old('universite_id') == $universite->id)>{{ $universite->nom }}</option>
                        @endforeach
                    </select>
                    @error('universite_id')<small class="erreur">{{ $message }}</small>@enderror
                </div>
                <div class="champ">
                    <label for="niveau">Mon niveau</label>
                    <select id="niveau" name="niveau" required>
                        <option value="">Choisir…</option>
                        @foreach ($niveaux as $niveau)
                            <option value="{{ $niveau }}" @selected(old('niveau') === $niveau)>{{ $niveau }}</option>
                        @endforeach
                    </select>
                    @error('niveau')<small class="erreur">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="champ">
                <label for="name">Nom complet</label>
                <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required>
                @error('name')<small class="erreur">{{ $message }}</small>@enderror
            </div>

            <div class="champ">
                <label for="email">Adresse e-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                @error('email')<small class="erreur">{{ $message }}</small>@enderror
            </div>

            <div class="champ">
                <label for="telephone">Téléphone (Mobile Money)</label>
                <input id="telephone" type="tel" name="telephone" value="{{ old('telephone') }}" placeholder="06 123 45 67" autocomplete="tel" required>
                @error('telephone')<small class="erreur">{{ $message }}</small>@enderror
            </div>

            <div class="ligne-2">
                <div class="champ">
                    <label for="password">Mot de passe</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" required>
                    @error('password')<small class="erreur">{{ $message }}</small>@enderror
                </div>
                <div class="champ">
                    <label for="password_confirmation">Confirmation</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                </div>
            </div>

            <div class="champ">
                <label for="code_parrain">Code de parrainage (facultatif)</label>
                <input id="code_parrain" name="code_parrain" value="{{ old('code_parrain', request('ref')) }}">
                @error('code_parrain')<small class="erreur">{{ $message }}</small>@enderror
            </div>

            <button type="submit" class="btn btn-primaire btn-bloc btn-lg">Créer mon compte</button>
            <p class="lien-bas">Déjà inscrit ? <a href="{{ route('connexion') }}">Se connecter</a></p>
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script>
    // La couleur de la page suit la filière choisie
    const selectFiliere = document.getElementById('filiere_id');
    const appliquerTheme = () => {
        const option = selectFiliere.options[selectFiliere.selectedIndex];
        document.documentElement.dataset.theme = option && option.dataset.theme ? option.dataset.theme : 'mayele';
    };
    selectFiliere.addEventListener('change', appliquerTheme);
    appliquerTheme();
</script>
@endpush