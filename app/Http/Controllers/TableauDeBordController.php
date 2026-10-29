<?php

namespace App\Http\Controllers;

use App\Models\ClasseVirtuelle;
use App\Models\Matiere;
use App\Models\Progression;
use App\Services\ClassementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class TableauDeBordController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load(['filiere', 'universite']);
        $filiere = $user->filiere;

        $matieres = collect();
        $termines = collect();
        $enCours = collect();
        $abonnement = null;
        $joursRestants = null;
        $plans = collect();
        $prochainDirect = null;
        $points = null;
        $nbTermines = 0;

        if ($filiere !== null) {
            $liste = Matiere::query()
                ->publiee()
                ->where('filiere_id', $filiere->id)
                ->where('niveau', $user->niveau)
                ->withCount(['chapitres as chapitres_total' => fn ($q) => $q->where('publie', true)])
                ->orderBy('semestre')
                ->orderBy('ordre')
                ->get();

            $termines = Progression::query()
                ->where('progressions.user_id', $user->id)
                ->whereNotNull('progressions.termine_le')
                ->join('chapitres', 'chapitres.id', '=', 'progressions.chapitre_id')
                ->where('chapitres.publie', true)
                ->whereIn('chapitres.matiere_id', $liste->pluck('id'))
                ->selectRaw('chapitres.matiere_id as matiere_id, COUNT(*) as total, MAX(progressions.termine_le) as derniere')
                ->groupBy('chapitres.matiere_id')
                ->get()
                ->keyBy('matiere_id');

            $idsTermines = Progression::where('user_id', $user->id)->whereNotNull('termine_le')->pluck('chapitre_id')->all();
            $nbTermines = count($idsTermines);

            // « Reprendre » : matières commencées, la plus récente d'abord, avec le prochain chapitre à faire
            $enCours = $liste
                ->filter(function (Matiere $m) use ($termines) {
                    $fait = (int) ($termines[$m->id]->total ?? 0);

                    return $fait > 0 && $fait < $m->chapitres_total;
                })
                ->sortByDesc(fn (Matiere $m) => $termines[$m->id]->derniere ?? '')
                ->take(3)
                ->map(function (Matiere $m) use ($termines, $idsTermines) {
                    return [
                        'matiere' => $m,
                        'fait' => (int) $termines[$m->id]->total,
                        'total' => $m->chapitres_total,
                        'suivant' => $m->chapitres()->where('publie', true)->whereNotIn('id', $idsTermines)->orderBy('ordre')->first(),
                    ];
                })
                ->values();

            $matieres = $liste->groupBy('semestre');

            $abonnement = $user->abonnements()
                ->actif()
                ->whereHas('plan', fn ($q) => $q->where('filiere_id', $filiere->id))
                ->with('plan')
                ->orderByDesc('fin_le')
                ->first();

            if ($abonnement) {
                $joursRestants = max(0, (int) ceil(now()->floatDiffInDays($abonnement->fin_le, false)));
            }

            $plans = $filiere->plans()->where('actif', true)->orderBy('duree_jours')->get();

            if (class_exists(ClasseVirtuelle::class) && Route::has('direct.index')) {
                $prochainDirect = ClasseVirtuelle::prochaine($filiere->id);
            }

            if (class_exists(ClassementService::class) && Route::has('classement')) {
                $points = (int) app(ClassementService::class)->points(null, 'tout')->get($user->id, 0);
            }
        }

        return view('tableau-de-bord', compact(
            'user', 'filiere', 'matieres', 'termines', 'enCours', 'abonnement', 'joursRestants',
            'plans', 'prochainDirect', 'points', 'nbTermines'
        ));
    }
}