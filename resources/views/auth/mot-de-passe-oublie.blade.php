@extends('layouts.app')

@section('titre', 'Mot de passe oublié')

@section('contenu')
<div class="auth-split">
    <aside class="auth-visuel">
        <h2>Pas de panique</h2>
        <p>Tu reçois un e-mail avec un lien pour choisir un nouveau mot de passe.</p>
        <ul class="auth-liste">
            <li>✓ Le lien est valable {{ $minutes }} minutes</li>
            <li>✓ Regarde aussi tes courriers indésirables</li>
        </ul>
    </aside>

    <section class="auth-form">
        <form class="formulaire" method="POST" action="{{ route('mot-de-passe.envoyer') }}">
            @csrf
            <h1>Mot de passe oublié</h1>

            @if (session('statut'))
                <div class="alerte alerte-ok" style="margin-bottom:1rem">{{ session('statut') }}</div>
            @endif

            <p class="muted">Entre l'adresse e-mail utilisée à ton inscription.</p>

            <div class="champ">
                <label for="email">Adresse e-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                @error('email')<small class="erreur">{{ $message }}</small>@enderror
            </div>

            <button type="submit" class="btn btn-primaire btn-bloc btn-lg">Envoyer le lien</button>

            <p class="lien-bas">
                Tu n'as plus accès à cet e-mail ?
                @if ($lienSupport)
                    <a href="{{ $lienSupport }}" target="_blank" rel="noopener">Contacte le support</a>
                @else
                    Contacte le support depuis la page <a href="{{ route('aide') }}">Aide</a>
                @endif
            </p>
            <p class="lien-bas"><a href="{{ route('connexion') }}">← Retour à la connexion</a></p>
        </form>
    </section>
</div>
@endsection