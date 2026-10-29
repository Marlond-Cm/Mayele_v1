<?php

namespace App\Http\Controllers;

use App\Models\Exercice;
use App\Models\ExerciceProgression;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AtelierController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $exercices = Exercice::query()
            ->where('publie', true)
            ->with(['filiere', 'matiere'])
            ->when(isset(Exercice::LANGAGES[$request->query('langage')]), fn ($q) => $q->where('langage', $request->query('langage')))
            ->when(isset(Exercice::DIFFICULTES[$request->query('difficulte')]), fn ($q) => $q->where('difficulte', $request->query('difficulte')))
            ->orderBy('ordre')->orderBy('id')
            ->get();

        $progressions = $user
            ? ExerciceProgression::where('user_id', $user->id)->whereIn('exercice_id', $exercices->pluck('id'))->get()->keyBy('exercice_id')
            : collect();

        $abonnes = $user
            ? $user->abonnements()->actif()->with('plan')->get()->pluck('plan.filiere_id')->unique()->values()->all()
            : [];

        return view('atelier.index', [
            'exercices' => $exercices,
            'progressions' => $progressions,
            'abonnes' => $abonnes,
            'user' => $user,
            'langage' => $request->query('langage'),
            'difficulte' => $request->query('difficulte'),
        ]);
    }

    public function show(Request $request, Exercice $exercice): View|RedirectResponse
    {
        $user = $request->user();

        abort_unless($exercice->publie || $user->estAdmin(), 404);

        $exercice->load(['filiere', 'matiere', 'chapitre']);

        if (! $exercice->estAccessiblePour($user)) {
            return redirect()
                ->route('abonnement.index', ['filiere' => $exercice->filiere->slug])
                ->with('erreur', 'Cet exercice est réservé aux abonnés de la filière.');
        }

        $progression = ExerciceProgression::where('exercice_id', $exercice->id)->where('user_id', $user->id)->first();

        $suivant = Exercice::query()
            ->where('publie', true)
            ->where('filiere_id', $exercice->filiere_id)
            ->where(fn ($q) => $q->where('ordre', '>', $exercice->ordre)
                ->orWhere(fn ($w) => $w->where('ordre', $exercice->ordre)->where('id', '>', $exercice->id)))
            ->orderBy('ordre')->orderBy('id')
            ->first();

        return view('atelier.show', [
            'exercice' => $exercice,
            'progression' => $progression,
            'suivant' => $suivant,
            'codeInitial' => $progression?->code ?? (string) $exercice->code_depart,
            'theme' => $exercice->filiere->theme,
            'client' => [
                'id' => $exercice->id,
                'langage' => $exercice->langage,
                'tests' => $exercice->tests,
                'codeDepart' => (string) $exercice->code_depart,
                'aProgression' => $progression !== null && filled($progression->code),
                'urlValider' => route('atelier.valider', $exercice->slug),
                'urlSolution' => route('atelier.solution', $exercice->slug),
                'urlSuivant' => $suivant ? route('atelier.show', $suivant->slug) : null,
                'csrf' => csrf_token(),
                'delai' => (int) config('atelier.delai_execution_ms'),
                'pyodideUrl' => config('atelier.pyodide_url'),
                'sqljs' => config('atelier.sqljs_base'),
                'solutionPossible' => (bool) $progression?->solutionDisponible() && filled($exercice->solution),
                'dejaReussi' => (bool) $progression?->reussi,
            ],
        ]);
    }

    /** Enregistre le résultat d'une validation (calculée dans le navigateur de l'étudiant). */
    public function valider(Request $request, Exercice $exercice): JsonResponse
    {
        $user = $request->user();

        abort_unless($exercice->publie || $user->estAdmin(), 404);
        abort_unless($exercice->estAccessiblePour($user), 403);

        $donnees = $request->validate([
            'code' => ['required', 'string', 'max:20000'],
            'tests_reussis' => ['required', 'integer', 'min:0'],
            'tests_total' => ['required', 'integer', 'min:1'],
        ]);

        $total = count($exercice->tests ?? []);

        if ($total === 0 || (int) $donnees['tests_total'] !== $total) {
            return response()->json(['message' => 'L\'exercice a été modifié : recharge la page.'], 422);
        }

        $reussis = min((int) $donnees['tests_reussis'], $total);

        $progression = ExerciceProgression::firstOrNew(['exercice_id' => $exercice->id, 'user_id' => $user->id]);
        $progression->code = $donnees['code'];
        $progression->tentatives = (int) $progression->tentatives + 1;

        if ($reussis === $total && ! $progression->reussi) {
            $progression->reussi = true;
            $progression->reussi_le = now();
        }

        $progression->save();

        return response()->json([
            'reussi' => $progression->reussi,
            'tentatives' => $progression->tentatives,
            'solutionPossible' => $progression->solutionDisponible() && filled($exercice->solution),
        ]);
    }

    /** La solution n'est envoyée qu'après 2 essais (ou une réussite). */
    public function solution(Request $request, Exercice $exercice): JsonResponse
    {
        $user = $request->user();

        abort_unless($exercice->publie || $user->estAdmin(), 404);
        abort_unless($exercice->estAccessiblePour($user), 403);

        $progression = ExerciceProgression::where('exercice_id', $exercice->id)->where('user_id', $user->id)->first();

        if ($progression === null || ! $progression->solutionDisponible()) {
            return response()->json(['message' => 'Essaie d\'abord au moins 2 fois : la solution se débloque ensuite.'], 403);
        }

        if (blank($exercice->solution)) {
            return response()->json(['message' => 'Aucune solution n\'est disponible pour cet exercice.'], 404);
        }

        $progression->update(['solution_vue' => true]);

        return response()->json(['solution' => $exercice->solution]);
    }
}