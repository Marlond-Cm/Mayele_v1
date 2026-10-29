<?php

namespace App\Http\Controllers;

use App\Models\Recompense;
use App\Services\ClassementService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParrainageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $filleuls = $user->filleuls()
            ->withExists(['paiements as a_paye' => fn ($q) => $q->where('statut', 'reussi')->where('montant', '>', 0)])
            ->latest()
            ->get()
            ->map(function ($filleul) {
                $filleul->nom_public = ClassementService::nomPublic($filleul->name);

                return $filleul;
            });

        $recompenses = Recompense::where('user_id', $user->id)->latest()->get();

        return view('parrainage.index', [
            'user' => $user,
            'filleuls' => $filleuls,
            'nbPayants' => $filleuls->where('a_paye', true)->count(),
            'recompenses' => $recompenses,
            'joursGagnes' => (int) $recompenses->sum('jours'),
            'annalesRecompensees' => $recompenses->where('type', 'annale')->count(),
        ]);
    }
}