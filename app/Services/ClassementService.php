<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ClassementService
{
    /**
     * Points par étudiant (user_id => points), du meilleur au moins bon.
     * Seul le meilleur score de chaque quiz est compté : refaire un quiz ne fait pas farmer de points.
     */
    public function points(?int $filiereId = null, string $periode = 'tout'): Collection
    {
        $depuis = match ($periode) {
            'semaine' => now()->startOfWeek(),
            'mois' => now()->startOfMonth(),
            default => null,
        };

        // Le cache ne contient qu'un tableau PHP (jamais un objet) : Laravel 13 refuse
        // de relire des objets depuis le cache, ils reviendraient en __PHP_Incomplete_Class.
        $tableau = Cache::remember("classement:v2:{$filiereId}:{$periode}", 300, function () use ($filiereId, $depuis): array {
            $meilleurs = DB::table('quiz_tentatives as t')
                ->join('quizzes as q', 'q.id', '=', 't.quiz_id')
                ->join('matieres as m', 'm.id', '=', 'q.matiere_id')
                ->whereNotNull('t.termine_le')
                ->when($filiereId, fn ($q) => $q->where('m.filiere_id', $filiereId))
                ->when($depuis, fn ($q) => $q->where('t.termine_le', '>=', $depuis))
                ->groupBy('t.user_id', 't.quiz_id')
                ->selectRaw('t.user_id, t.quiz_id, MAX(t.score) as meilleur');

            return DB::query()
                ->fromSub($meilleurs, 'b')
                ->groupBy('b.user_id')
                ->selectRaw('b.user_id, SUM(b.meilleur) as points')
                ->orderByDesc('points')
                ->pluck('points', 'user_id')
                ->map(fn ($p) => (int) $p)
                ->all();
        });

        return collect($tableau);
    }

    /**
     * Score d'une université = points de ses $topN meilleurs étudiants
     * (ne favorise pas les grandes universités).
     *
     * @return Collection<int, array{total: int, participants: int}>
     */
    public function universites(Collection $points, int $topN = 10): Collection
    {
        $users = User::query()
            ->whereIn('id', $points->keys())
            ->whereNotNull('universite_id')
            ->get(['id', 'universite_id'])
            ->keyBy('id');

        return $points
            ->filter(fn ($p, $id) => isset($users[$id]))
            ->map(fn ($p, $id) => ['universite_id' => $users[$id]->universite_id, 'points' => $p])
            ->groupBy('universite_id')
            ->map(fn (Collection $groupe) => [
                'total' => (int) $groupe->sortByDesc('points')->take($topN)->sum('points'),
                'participants' => $groupe->count(),
            ])
            ->sortByDesc('total');
    }

    /** « Marie Nzoussi » devient « Marie N. » : le classement ne montre pas les noms complets. */
    public static function nomPublic(string $nom): string
    {
        $mots = preg_split('/\s+/', trim($nom)) ?: [$nom];

        return count($mots) > 1
            ? $mots[0].' '.mb_strtoupper(mb_substr(end($mots), 0, 1)).'.'
            : $mots[0];
    }
}