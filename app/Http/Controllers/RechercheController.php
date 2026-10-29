<?php

namespace App\Http\Controllers;

use App\Models\Chapitre;
use App\Models\Matiere;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RechercheController extends Controller
{
    public function __invoke(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $matieres = collect();
        $chapitres = collect();

        if (mb_strlen($q) >= 2) {
            // Les caractères spéciaux de LIKE sont neutralisés
            $motif = '%'.addcslashes($q, '%_\\').'%';

            $matieres = Matiere::query()
                ->publiee()
                ->with('filiere')
                ->withCount(['chapitres as chapitres_total' => fn ($c) => $c->where('publie', true)])
                ->where(fn ($w) => $w->where('nom', 'like', $motif)->orWhere('description', 'like', $motif)->orWhere('parcours', 'like', $motif))
                ->orderBy('nom')
                ->limit(24)
                ->get();

            $chapitres = Chapitre::query()
                ->publie()
                ->whereHas('matiere', fn ($m) => $m->where('publie', true))
                ->with('matiere.filiere')
                ->where('titre', 'like', $motif)
                ->orderBy('titre')
                ->limit(30)
                ->get();
        }

        return view('recherche.index', compact('q', 'matieres', 'chapitres'));
    }
}