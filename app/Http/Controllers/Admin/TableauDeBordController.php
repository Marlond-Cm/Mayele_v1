<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\Annale;
use App\Models\Filiere;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\View\View;

class TableauDeBordController extends Controller
{
    public function __invoke(): View
    {
        $debutMois = now()->startOfMonth();

        $stats = [
            'etudiants' => User::where('role', 'etudiant')->count(),
            'nouveaux' => User::where('role', 'etudiant')->where('created_at', '>=', $debutMois)->count(),
            'abonnes' => Abonnement::actif()->distinct()->count('user_id'),
            'revenu_mois' => (int) Paiement::where('statut', 'reussi')->where('paye_le', '>=', $debutMois)->sum('montant'),
            'revenu_total' => (int) Paiement::where('statut', 'reussi')->sum('montant'),
            'annales_attente' => Annale::where('statut', 'en_attente')->count(),
        ];

        return view('admin.tableau-de-bord', [
            'stats' => $stats,
            'filieres' => Filiere::withCount('etudiants')->orderBy('ordre')->get(),
            'derniersPaiements' => Paiement::with('user', 'abonnement.plan.filiere')->latest()->limit(8)->get(),
        ]);
    }
}