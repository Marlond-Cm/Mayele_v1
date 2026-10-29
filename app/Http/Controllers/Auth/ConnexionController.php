<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConnexionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConnexionController extends Controller
{
    public function create(): View
    {
        return view('auth.connexion');
    }

    public function store(ConnexionRequest $request): RedirectResponse
    {
        $identifiant = trim($request->input('identifiant'));
        $cle = Str::lower($identifiant).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($cle, 5)) {
            $secondes = RateLimiter::availableIn($cle);

            throw ValidationException::withMessages([
                'identifiant' => "Trop de tentatives. Réessaie dans {$secondes} secondes.",
            ]);
        }

        $champ = filter_var($identifiant, FILTER_VALIDATE_EMAIL) ? 'email' : 'telephone';

        if ($champ === 'telephone') {
            $identifiant = preg_replace('/\s+/', '', $identifiant);
        }

        $reussi = Auth::attempt(
            [$champ => $identifiant, 'password' => $request->input('password')],
            $request->boolean('se_souvenir')
        );

        if (! $reussi) {
            RateLimiter::hit($cle, 60);

            throw ValidationException::withMessages([
                'identifiant' => 'Identifiants incorrects.',
            ]);
        }

        RateLimiter::clear($cle);
        $request->session()->regenerate();

        return redirect()->intended(route('tableau-de-bord'));
    }
}