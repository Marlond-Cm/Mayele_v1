@extends('layouts.app')

@section('titre', 'Connexion')

@section('contenu')
<div class="auth-split">
    <aside class="auth-visuel">
        <h2>Content de te revoir</h2>
        <p>Reprends ta formation là où tu t'es arrêté.</p>
        <ul class="auth-liste">
            <li>✓ Retrouve tes cours et ta progression</li>
            <li>✓ Rejoins tes cours en direct</li>
            <li>✓ Entraîne-toi avec les annales et les quiz</li>
        </ul>
    </aside>

    <section class="auth-form">
        <form class="formulaire" method="POST" action="{{ route('connexion') }}">
            @csrf
            <h1>Connexion</h1>

            @if (session('statut'))
                <div class="alerte alerte-ok" style="margin-bottom:1rem">{{ session('statut') }}</div>
            @endif

            <p class="muted">Entre ton e-mail ou ton numéro de téléphone.</p>

            <div class="champ">
                <label for="identifiant">E-mail ou téléphone</label>
                <input id="identifiant" name="identifiant" value="{{ old('identifiant') }}" autocomplete="username" required autofocus>
                @error('identifiant')<small class="erreur">{{ $message }}</small>@enderror
            </div>

            <div class="champ">
                <label for="password">Mot de passe</label>
                <input id="password" type="password" name="password" autocomplete="current-password" required>
                @error('password')<small class="erreur">{{ $message }}</small>@enderror
                <small><a href="{{ route('mot-de-passe.oublie') }}">Mot de passe oublié ?</a></small>
            </div>

            <div class="champ">
                <label class="case"><input type="checkbox" name="se_souvenir" value="1"> Rester connecté</label>
            </div>

            <button type="submit" class="btn btn-primaire btn-bloc btn-lg">Se connecter</button>
            <p class="lien-bas">Pas encore de compte ? <a href="{{ route('inscription') }}">S'inscrire</a></p>
        </form>
    </section>
</div>
@endsection