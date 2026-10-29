<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\InscriptionRequest;
use App\Models\Filiere;
use App\Models\Universite;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InscriptionController extends Controller
{
    public function create(): View
    {
        return view('auth.inscription', [
            'filieres' => Filiere::where('actif', true)->orderBy('ordre')->get(),
            'universites' => Universite::where('actif', true)->orderBy('nom')->get(),
            'niveaux' => ['L1', 'L2', 'L3'],
        ]);
    }

    public function store(InscriptionRequest $request): RedirectResponse
    {
        $donnees = $request->validated();

        $parrain = ! empty($donnees['code_parrain'])
            ? User::where('code_parrainage', $donnees['code_parrain'])->first()
            : null;

        $user = User::create([
            'name' => $donnees['name'],
            'email' => $donnees['email'],
            'telephone' => $donnees['telephone'],
            'universite_id' => $donnees['universite_id'],
            'filiere_id' => $donnees['filiere_id'],
            'niveau' => $donnees['niveau'],
            'password' => $donnees['password'],
            'parrain_id' => $parrain?->id,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('tableau-de-bord');
    }
}