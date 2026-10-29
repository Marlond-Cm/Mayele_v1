<?php

namespace App\Http\Controllers;

use App\Models\ClasseVirtuelle;
use App\Models\ClassePresence;
use App\Models\Filiere;
use App\Models\User;
use App\Services\VisioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DirectController extends Controller
{
    public function __construct(private VisioService $visio)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $filiere = $request->filled('filiere')
            ? Filiere::where('slug', $request->query('filiere'))->where('actif', true)->first()
            : null;

        $base = fn () => ClasseVirtuelle::query()
            ->with(['filiere', 'matiere', 'animateur'])
            ->when($filiere, fn ($q) => $q->where('filiere_id', $filiere->id));

        $aVenir = $base()->aVenir()->orderBy('debut_le')->get();

        $replays = $base()->terminees()
            ->where('statut', 'planifie')
            ->whereNotNull('replay_url')
            ->orderByDesc('debut_le')
            ->limit(12)
            ->get();

        $abonnes = $user
            ? $user->abonnements()->actif()->with('plan')->get()->pluck('plan.filiere_id')->unique()->values()->all()
            : [];

        return view('direct.index', [
            'aVenir' => $aVenir,
            'replays' => $replays,
            'filiere' => $filiere,
            'filieres' => Filiere::where('actif', true)->orderBy('ordre')->get(),
            'user' => $user,
            'abonnes' => $abonnes,
            'theme' => $filiere?->theme,
        ]);
    }

    public function show(Request $request, ClasseVirtuelle $classe): View
    {
        $classe->load(['filiere', 'matiere', 'animateur']);
        $user = $request->user();

        $refus = $user ? $this->refus($classe, $user) : null;

        return view('direct.show', [
            'classe' => $classe,
            'user' => $user,
            'etat' => $classe->etat(),
            'moderateur' => $classe->estModerateur($user),
            'accessible' => $classe->estAccessiblePour($user),
            'peutRejoindre' => $user !== null && $refus === null && $this->visio->configure(),
            'visioPrete' => $this->visio->configure(),
            'ouvertureIso' => $classe->debut_le->copy()
                ->subMinutes((int) config('visio.ouverture_avant_minutes'))->toIso8601String(),
            'theme' => $classe->filiere->theme,
        ]);
    }

    public function salle(Request $request, ClasseVirtuelle $classe): View|RedirectResponse
    {
        $user = $request->user();
        $classe->load('filiere');

        if (($message = $this->refus($classe, $user)) !== null) {
            return redirect()->route('direct.show', $classe)->with('erreur', $message);
        }

        if (! $this->visio->configure()) {
            return redirect()->route('direct.show', $classe)
                ->with('erreur', 'La visioconférence n\'est pas encore configurée. Réessaie plus tard.');
        }

        $this->noterPresence($classe, $user);

        return view('direct.salle', [
            'classe' => $classe,
            'options' => $this->visio->options($classe, $user, $classe->estModerateur($user), $request->boolean('audio')),
            'theme' => $classe->filiere->theme,
        ]);
    }

    /** Signal de présence envoyé toutes les minutes depuis la salle. */
    public function presence(Request $request, ClasseVirtuelle $classe): JsonResponse
    {
        $user = $request->user();

        abort_unless($this->refus($classe, $user) === null, 403);

        $this->noterPresence($classe, $user);

        return response()->json(['ok' => true]);
    }

    /** Fichier .ics pour ajouter le cours à son agenda. */
    public function calendrier(ClasseVirtuelle $classe): Response
    {
        $debut = $classe->debut_le->copy()->utc();
        $fin = $classe->fin_le->copy()->utc();
        $hote = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'mayele';

        $lignes = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//'.config('app.name').'//Direct//FR',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:direct-'.$classe->id.'@'.$hote,
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$debut->format('Ymd\THis\Z'),
            'DTEND:'.$fin->format('Ymd\THis\Z'),
            'SUMMARY:'.$this->echapper($classe->titre),
            'DESCRIPTION:'.$this->echapper('Cours en direct sur '.config('app.name').' : '.route('direct.show', $classe)),
            'URL:'.route('direct.show', $classe),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return response(implode("\r\n", $lignes)."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="direct-'.$classe->id.'.ics"',
        ]);
    }

    /** Retourne le message de refus, ou null si l'utilisateur peut entrer dans la salle. */
    private function refus(ClasseVirtuelle $classe, User $user): ?string
    {
        if ($classe->statut === 'annule') {
            return 'Ce cours en direct a été annulé.';
        }

        if (! $classe->estAccessiblePour($user)) {
            return 'Ce cours en direct est réservé aux abonnés de la filière.';
        }

        if (! $classe->estModerateur($user)) {
            return match ($classe->etat()) {
                'a_venir' => 'La salle n\'est pas encore ouverte : elle ouvre '.config('visio.ouverture_avant_minutes').' minutes avant le début.',
                'terminee' => 'Ce cours en direct est terminé.',
                default => null,
            };
        }

        return null;
    }

    private function noterPresence(ClasseVirtuelle $classe, User $user): void
    {
        ClassePresence::upsert(
            [[
                'classe_virtuelle_id' => $classe->id,
                'user_id' => $user->id,
                'premiere_entree_at' => now(),
                'derniere_presence_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]],
            ['classe_virtuelle_id', 'user_id'],
            ['derniere_presence_at', 'updated_at']
        );
    }

    private function echapper(string $texte): string
    {
        return str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\;', '\,', '\n', '\n'], $texte);
    }
}