<?php

namespace App\Http\Controllers;

use App\Mail\ReinitialisationMotDePasse;
use App\Models\User;
use App\Services\AideService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class MotDePasseController extends Controller
{
    public function demande(): View
    {
        return view('auth.mot-de-passe-oublie', [
            'lienSupport' => class_exists(AideService::class) ? app(AideService::class)->lienSupport() : null,
            'minutes' => (int) config('auth.passwords.users.expire', 60),
        ]);
    }

    public function envoyer(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Entre ton adresse e-mail.',
            'email.email' => 'Cette adresse e-mail est invalide.',
        ]);

        try {
            Password::sendResetLink(['email' => $donnees['email']], function (User $user, string $token) {
                $lien = route('mot-de-passe.reinitialiser', ['token' => $token, 'email' => $user->email]);

                Mail::to($user->email)->send(new ReinitialisationMotDePasse(
                    $user,
                    $lien,
                    (int) config('auth.passwords.users.expire', 60)
                ));
            });
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->withErrors([
                'email' => 'L\'envoi de l\'e-mail a échoué pour le moment. Réessaie dans quelques minutes ou contacte le support.',
            ]);
        }

        // Même réponse que l'adresse existe ou non : on ne révèle pas qui a un compte
        return back()->with('statut', 'Si un compte existe avec cette adresse, un e-mail vient de t\'être envoyé. Pense à regarder tes courriers indésirables.');
    }

    public function formulaire(Request $request, string $token): View
    {
        return view('auth.reinitialiser', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function reinitialiser(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'Adresse e-mail manquante.',
            'password.required' => 'Choisis un mot de passe.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
        ]);

        $statut = Password::reset($donnees, function (User $user, string $motDePasse) {
            $user->forceFill([
                'password' => $motDePasse,
                'remember_token' => Str::random(60),
            ])->save();

            // Déconnecte les autres appareils
            try {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            } catch (Throwable $e) {
                report($e);
            }

            event(new PasswordReset($user));
        });

        if ($statut === Password::PASSWORD_RESET) {
            return redirect()->route('connexion')->with('statut', 'Ton mot de passe a été changé. Tu peux maintenant te connecter.');
        }

        return back()->withInput($request->only('email'))->withErrors([
            'email' => 'Ce lien est invalide ou a expiré. Demande un nouveau lien.',
        ]);
    }
}