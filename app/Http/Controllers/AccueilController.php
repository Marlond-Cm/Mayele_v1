<?php

namespace App\Http\Controllers;

use App\Models\Annale;
use App\Models\Chapitre;
use App\Models\Filiere;
use App\Models\Matiere;
use App\Models\Universite;
use Illuminate\View\View;

class AccueilController extends Controller
{
    public function __invoke(): View
    {
        $filieres = Filiere::query()
            ->where('actif', true)
            ->orderBy('ordre')
            ->with(['plans' => fn ($q) => $q->where('actif', true)->orderBy('duree_jours')])
            ->get();

        // Chiffres réels uniquement : un chiffre à zéro n'est pas affiché
        $chiffres = array_filter([
            'Matières' => Matiere::where('publie', true)->count(),
            'Chapitres' => Chapitre::where('publie', true)->count(),
            'Annales corrigées' => Annale::where('statut', 'valide')->count(),
            'Universités' => Universite::where('actif', true)->count(),
        ]);

        $universites = Universite::query()
            ->where('actif', true)
            ->orderBy('nom')
            ->limit(12)
            ->get(['nom', 'sigle']);

        return view('accueil', compact('filieres', 'chiffres', 'universites'));
    }
}