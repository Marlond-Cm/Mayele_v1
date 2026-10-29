<?php

namespace App\Http\Controllers;

use App\Models\Filiere;
use App\Models\Progression;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FiliereController extends Controller
{
    public function show(Request $request, Filiere $filiere): View
    {
        abort_unless($filiere->actif, 404);

        $user = $request->user();

        $matieres = $filiere->matieres()
            ->publiee()
            ->withCount(['chapitres as chapitres_total' => fn ($q) => $q->where('publie', true)])
            ->orderBy('niveau')
            ->orderBy('semestre')
            ->orderBy('ordre')
            ->get();

        $faits = $user
            ? Progression::query()
                ->where('progressions.user_id', $user->id)
                ->whereNotNull('progressions.termine_le')
                ->join('chapitres', 'chapitres.id', '=', 'progressions.chapitre_id')
                ->where('chapitres.publie', true)
                ->whereIn('chapitres.matiere_id', $matieres->pluck('id'))
                ->selectRaw('chapitres.matiere_id as matiere_id, COUNT(*) as total')
                ->groupBy('chapitres.matiere_id')
                ->pluck('total', 'matiere_id')
            : collect();

        return view('filieres.show', [
            'filiere' => $filiere,
            'matieres' => $matieres->groupBy('niveau'),
            'nbMatieres' => $matieres->count(),
            'totalChapitres' => (int) $matieres->sum('chapitres_total'),
            'faits' => $faits,
            'plans' => $filiere->plans()->where('actif', true)->orderBy('duree_jours')->get(),
            'theme' => $filiere->theme,
        ]);
    }
}