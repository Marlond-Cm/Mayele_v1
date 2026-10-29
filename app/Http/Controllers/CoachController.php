<?php

namespace App\Http\Controllers;

use App\Exceptions\CoachException;
use App\Models\Chapitre;
use App\Models\CoachConversation;
use App\Models\Matiere;
use App\Services\CoachService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CoachController extends Controller
{
    public function __construct(private CoachService $coach)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('coach.index', [
            'conversations' => CoachConversation::where('user_id', $user->id)
                ->with(['matiere', 'chapitre'])
                ->latest('updated_at')
                ->limit(30)
                ->get(),
            'restant' => $this->coach->restant($user),
            'limite' => (int) config('mayele.coach.messages_par_jour'),
        ]);
    }

    public function demarrer(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'chapitre_id' => ['nullable', Rule::exists('chapitres', 'id')->where('publie', true)],
            'matiere_id' => ['nullable', Rule::exists('matieres', 'id')->where('publie', true)],
            'mode' => ['required', Rule::in(array_keys(CoachConversation::MODES))],
        ]);

        $chapitre = filled($donnees['chapitre_id'] ?? null)
            ? Chapitre::with('matiere.filiere')->find($donnees['chapitre_id'])
            : null;

        $matiere = $chapitre?->matiere
            ?? (filled($donnees['matiere_id'] ?? null) ? Matiere::with('filiere')->find($donnees['matiere_id']) : null);

        abort_if($matiere === null || ! $matiere->publie, 404);

        $user = $request->user();

        if (! $this->coach->peutUtiliser($user, $matiere->filiere_id)) {
            return redirect()
                ->route('abonnement.index', ['filiere' => $matiere->filiere->slug])
                ->with('erreur', 'Le coach IA est réservé aux abonnés de cette filière.');
        }

        // Une conversation par étudiant, chapitre (ou matière) et mode : l'historique est conservé
        $conversation = CoachConversation::firstOrCreate(
            [
                'user_id' => $user->id,
                'matiere_id' => $matiere->id,
                'chapitre_id' => $chapitre?->id,
                'mode' => $donnees['mode'],
            ],
            ['titre' => $chapitre?->titre ?? $matiere->nom]
        );

        return redirect()->route('coach.show', $conversation);
    }

    public function show(Request $request, CoachConversation $conversation): View
    {
        abort_unless($conversation->user_id === $request->user()->id, 404);

        $conversation->load(['matiere.filiere', 'chapitre', 'messages']);
        $user = $request->user();

        return view('coach.show', [
            'conversation' => $conversation,
            'autorise' => $this->coach->peutUtiliser($user, $conversation->matiere->filiere_id),
            'restant' => $this->coach->restant($user),
            'theme' => $conversation->matiere->filiere->theme,
        ]);
    }

    public function envoyer(Request $request, CoachConversation $conversation): JsonResponse
    {
        abort_unless($conversation->user_id === $request->user()->id, 404);

        $conversation->load('matiere');

        if (! $this->coach->peutUtiliser($request->user(), $conversation->matiere->filiere_id)) {
            return response()->json(['message' => 'Ton abonnement a expiré. Renouvelle-le pour continuer avec le coach.'], 403);
        }

        $donnees = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:'.config('mayele.coach.max_caracteres')],
        ], [
            'message.required' => 'Écris ta question.',
            'message.min' => 'Ton message est trop court.',
            'message.max' => 'Ton message est trop long (maximum '.config('mayele.coach.max_caracteres').' caractères).',
        ]);

        try {
            $reponse = $this->coach->repondre($conversation, trim($donnees['message']));
        } catch (CoachException $e) {
            return response()->json(['message' => $e->getMessage()], $e->statut);
        }

        return response()->json([
            'html' => $reponse->html(),
            'restant' => $this->coach->restant($request->user()),
        ]);
    }

    public function destroy(Request $request, CoachConversation $conversation): RedirectResponse
    {
        abort_unless($conversation->user_id === $request->user()->id, 404);

        $conversation->delete();

        return redirect()->route('coach.index');
    }
}