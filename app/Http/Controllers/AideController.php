<?php

namespace App\Http\Controllers;

use App\Models\AideQuestion;
use App\Models\Filiere;
use App\Services\AideService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AideController extends Controller
{
    public function __construct(private AideService $aide)
    {
    }

    public function index(): View
    {
        return view('aide.index', [
            'categories' => $this->aide->categories(),
            'filieres' => Filiere::query()
                ->where('actif', true)
                ->orderBy('ordre')
                ->with(['plans' => fn ($q) => $q->where('actif', true)->orderBy('duree_jours')])
                ->get(),
            'lienSupport' => $this->aide->lienSupport(),
            'emailSupport' => config('aide.support.email'),
        ]);
    }

    public function question(Request $request): JsonResponse
    {
        $donnees = $request->validate([
            'question' => ['required', 'string', 'min:3', 'max:'.config('aide.assistant.max_caracteres')],
        ], [
            'question.required' => 'Écris ta question.',
            'question.min' => 'Ta question est trop courte.',
            'question.max' => 'Ta question est trop longue (maximum '.config('aide.assistant.max_caracteres').' caractères).',
        ]);

        $question = trim($donnees['question']);

        // L'historique reste côté serveur (session) : le navigateur ne peut pas l'altérer
        $historique = (array) $request->session()->get('aide.historique', []);

        $resultat = $this->aide->repondre($question, $request->user(), $this->visiteur($request), $historique);

        if ($resultat['source'] === 'ia') {
            $historique[] = ['role' => 'user', 'content' => $question];
            $historique[] = ['role' => 'assistant', 'content' => $resultat['texte']];
            $request->session()->put('aide.historique', array_slice($historique, -8));
        }

        return response()->json([
            'id' => $resultat['id'],
            'html' => $resultat['html'],
            'articles' => $resultat['articles'],
            'support' => $resultat['support'],
            'source' => $resultat['source'],
        ]);
    }

    public function reinitialiser(Request $request): JsonResponse
    {
        $request->session()->forget('aide.historique');

        return response()->json(['ok' => true]);
    }

    public function avis(Request $request, AideQuestion $question): JsonResponse
    {
        abort_unless($question->visiteur === $this->visiteur($request), 404);

        $donnees = $request->validate(['utile' => ['required', 'boolean']]);

        $question->update(['utile' => (bool) $donnees['utile']]);

        return response()->json(['ok' => true]);
    }

    /** Identifie le visiteur pour le quota : compte connecté, sinon session. */
    private function visiteur(Request $request): string
    {
        return $request->user()
            ? 'u'.$request->user()->id
            : 's'.substr(hash('sha256', $request->session()->getId()), 0, 40);
    }
}