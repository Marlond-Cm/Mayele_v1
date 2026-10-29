<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CoachException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QuizRequest;
use App\Models\Chapitre;
use App\Models\Matiere;
use App\Models\Quiz;
use App\Services\CoachService;
use App\Services\QuizParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function index(Request $request): View
    {
        $quizzes = Quiz::query()
            ->with('matiere.filiere')
            ->withCount(['questions', 'tentatives'])
            ->when($request->filled('q'), fn ($q) => $q->where('titre', 'like', '%'.$request->query('q').'%'))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.quiz.index', ['quizzes' => $quizzes]);
    }

    public function create(Request $request): View
    {
        return view('admin.quiz.form', [
            'quiz' => new Quiz([
                'points_par_question' => 10,
                'est_gratuit' => false,
                'publie' => false,
                'matiere_id' => $request->integer('matiere') ?: null,
            ]),
            'texte' => '',
            'matieres' => $this->matieres(),
        ]);
    }

    public function store(QuizRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $quiz = Quiz::create($request->safe()->except('questions'));
            $this->remplacerQuestions($quiz, $request->questionsParsees());
        });

        return redirect()->route('admin.quiz.index')->with('succes', 'Quiz créé.');
    }

    public function edit(Quiz $quiz): View
    {
        return view('admin.quiz.form', [
            'quiz' => $quiz,
            'texte' => QuizParser::serialiser($quiz),
            'matieres' => $this->matieres(),
        ]);
    }

    public function update(QuizRequest $request, Quiz $quiz): RedirectResponse
    {
        DB::transaction(function () use ($request, $quiz) {
            $quiz->update($request->safe()->except('questions'));
            $this->remplacerQuestions($quiz, $request->questionsParsees());
        });

        return redirect()->route('admin.quiz.index')->with('succes', 'Quiz mis à jour.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $quiz->delete();

        return redirect()->route('admin.quiz.index')->with('succes', 'Quiz supprimé.');
    }

    /** Génère un brouillon de quiz avec l'IA : l'admin relit et corrige avant d'enregistrer. */
    public function generer(Request $request, CoachService $coach): JsonResponse
    {
        $donnees = $request->validate([
            'matiere_id' => ['required', 'exists:matieres,id'],
            'sujet' => ['nullable', 'string', 'max:400'],
            'nombre' => ['required', 'integer', 'between:3,20'],
            'utiliser_chapitres' => ['nullable', 'boolean'],
        ], [
            'matiere_id.required' => 'Choisis d\'abord la matière.',
            'nombre.between' => 'Le nombre de questions doit être entre 3 et 20.',
        ]);

        $matiere = Matiere::findOrFail($donnees['matiere_id']);

        $contenu = null;

        if ($request->boolean('utiliser_chapitres')) {
            $contenu = $matiere->chapitres()
                ->where('publie', true)
                ->get()
                ->filter(fn (Chapitre $c) => filled($c->contenu))
                ->map(fn (Chapitre $c) => "## {$c->titre}\n{$c->contenu}")
                ->implode("\n\n");
        }

        if (blank($donnees['sujet'] ?? null) && blank($contenu)) {
            return response()->json([
                'message' => 'Indique un sujet, ou coche « utiliser les chapitres » (la matière doit avoir des chapitres publiés avec du contenu).',
            ], 422);
        }

        try {
            $texte = $coach->genererQuiz($matiere, $donnees['sujet'] ?? null, $contenu, (int) $donnees['nombre']);
        } catch (CoachException $e) {
            return response()->json(['message' => $e->getMessage()], $e->statut);
        }

        return response()->json([
            'questions' => $texte,
            'avertissements' => QuizParser::parse($texte)['erreurs'],
        ]);
    }

    private function remplacerQuestions(Quiz $quiz, array $questions): void
    {
        $quiz->questions()->delete(); // les choix suivent (suppression en cascade)

        foreach ($questions as $i => $donnees) {
            $question = $quiz->questions()->create([
                'enonce' => $donnees['enonce'],
                'explication' => $donnees['explication'],
                'ordre' => $i + 1,
            ]);

            foreach ($donnees['choix'] as $j => $choix) {
                $question->choix()->create([
                    'libelle' => $choix['libelle'],
                    'est_correct' => $choix['est_correct'],
                    'ordre' => $j + 1,
                ]);
            }
        }
    }

    private function matieres()
    {
        return Matiere::with('filiere')
            ->orderBy('filiere_id')->orderBy('niveau')->orderBy('semestre')->orderBy('ordre')
            ->get()
            ->groupBy(fn (Matiere $m) => $m->filiere->nom);
    }
}