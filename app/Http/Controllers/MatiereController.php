<?php

namespace App\Http\Controllers;

use App\Models\Matiere;
use App\Models\Progression;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatiereController extends Controller
{
    public function show(Request $request, Matiere $matiere): View
    {
        abort_unless($matiere->publie, 404);

        $matiere->load('filiere');
        $chapitres = $matiere->chapitres()->where('publie', true)->get();
        $user = $request->user();

        $termines = $user
            ? Progression::query()
                ->where('user_id', $user->id)
                ->whereNotNull('termine_le')
                ->whereIn('chapitre_id', $chapitres->pluck('id'))
                ->pluck('chapitre_id')
                ->all()
            : [];

        $abonne = $user?->aAbonnementActifPour($matiere->filiere_id) ?? false;

        $pourcentage = $chapitres->count() > 0
            ? (int) round(count($termines) / $chapitres->count() * 100)
            : 0;

        return view('matieres.show', [
            'matiere' => $matiere,
            'chapitres' => $chapitres,
            'termines' => $termines,
            'abonne' => $abonne,
            'pourcentage' => $pourcentage,
            'theme' => $matiere->filiere->theme,
        ]);
    }
}