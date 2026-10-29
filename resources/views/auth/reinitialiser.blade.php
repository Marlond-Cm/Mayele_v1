@extends('layouts.app')

@section('titre', 'Nouveau mot de passe')

@section('contenu')
<div class="auth-split">
    <aside class="auth-visuel">
        <h2>Un nouveau départ</h2>
        <p>Choisis un mot de passe que tu n'utilises nulle part ailleurs.</p>
        <ul class="auth-liste">
            <li>✓ 8 caractères minimum</li>
            <li>✓ Tes autres appareils seront déconnectés</li>
        </ul>
    </aside>

    <section class="auth-form">
        <form class="formulaire" method="POST" action="{{ route('mot-de-passe.mettre-a-jour') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <h1>Nouveau mot de passe</h1>

            <div class="champ">
                <label for="email">Adresse e-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" readonly>
                @error('email')<small class="erreur">{{ $message }}</small>@enderror
                @error('token')<small class="erreur">{{ $message }}</small>@enderror
            </div>

            <div class="champ">
                <label for="password">Nouveau mot de passe</label>
                <input id="password" type="password" name="password" autocomplete="new-password" required autofocus>
                @error('password')<small class="erreur">{{ $message }}</small>@enderror
            </div>

            <div class="champ">
                <label for="password_confirmation">Confirmation</label>
                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
            </div>

            <button type="submit" class="btn btn-primaire btn-bloc btn-lg">Changer mon mot de passe</button>
            <p class="lien-bas"><a href="{{ route('mot-de-passe.oublie') }}">Demander un nouveau lien</a></p>
        </form>
    </section>
</div>
@endsection