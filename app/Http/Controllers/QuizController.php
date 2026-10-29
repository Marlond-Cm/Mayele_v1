<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizTentative;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function show(Request $request, Quiz $quiz): View
    {
        $this->verifier($quiz);

        $quiz->load('matiere.filiere');
        $user = $request->user();
        $accessible = $quiz->estAccessiblePour($user);

        // Les bonnes réponses ne quittent JAMAIS le serveur avant la soumission
        $questions = $accessible && $user
            ? $quiz->questions()
                ->with(['choix' => fn ($q) => $q->select(['id', 'question_id', 'libelle'])])
                ->get()
                ->each(fn ($question) => $question->setRelation('choix', $question->choix->shuffle()))
            : collect();

        abort_if($quiz->questions()->doesntExist(), 404);

        $meilleur = $user
            ? $quiz->tentatives()->where('user_id', $user->id)->whereNotNull('termine_le')->max('score')
            : null;

        return view('quiz.show', [
            'quiz' => $quiz,
            'user' => $user,
            'accessible' => $accessible,
            'questions' => $questions,
            'nbQuestions' => $quiz->questions()->count(),
            'meilleur' => $meilleur,
            'theme' => $quiz->matiere->filiere->theme,
        ]);
    }

    public function soumettre(Request $request, Quiz $quiz): RedirectResponse
    {
        $this->verifier($quiz);

        $quiz->load('matiere');
        abort_unless($quiz->estAccessiblePour($request->user()), 403);

        $questions = $quiz->questions()->with('choix')->get();
        abort_if($questions->isEmpty(), 404);

        $envoyees = (array) $request->input('reponses', []);
        $choisies = [];
        $bonnes = 0;

        foreach ($questions as $question) {
            $choixId = isset($envoyees[$question->id]) ? (int) $envoyees[$question->id] : null;

            // Le choix doit appartenir à cette question : on ne fait pas confiance au formulaire
            $choisi = $question->choix->firstWhere('id', $choixId);
            $correct = $question->choix->firstWhere('est_correct', true);

            $choisies[$question->id] = $choisi?->id;

            if ($choisi && $correct && $choisi->id === $correct->id) {
                $bonnes++;
            }
        }

        $tentative = QuizTentative::create([
            'user_id' => $request->user()->id,
            'quiz_id' => $quiz->id,
            'score' => $bonnes * $quiz->points_par_question,
            'bonnes_reponses' => $bonnes,
            'total_questions' => $questions->count(),
            'reponses' => $choisies,
            'termine_le' => now(),
        ]);

        return redirect()->route('quiz.resultat', $tentative);
    }

    public function resultat(Request $request, QuizTentative $tentative): View
    {
        abort_unless($tentative->user_id === $request->user()->id, 404);

        $tentative->load('quiz.matiere.filiere');
        $quiz = $tentative->quiz;

        return view('quiz.resultat', [
            'tentative' => $tentative,
            'quiz' => $quiz,
            'questions' => $quiz->questions()->with('choix')->get(),
            'meilleur' => (int) $quiz->tentatives()->where('user_id', $tentative->user_id)->max('score'),
            'theme' => $quiz->matiere->filiere->theme,
        ]);
    }

    private function verifier(Quiz $quiz): void
    {
        $quiz->loadMissing('matiere');

        abort_unless($quiz->publie && $quiz->matiere->publie, 404);
    }
}