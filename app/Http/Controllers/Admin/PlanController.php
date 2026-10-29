<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        return view('admin.plans.index', [
            'filieres' => Filiere::with(['plans' => fn ($q) => $q->orderBy('duree_jours')])->orderBy('ordre')->get(),
        ]);
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $donnees = $request->validate([
            'prix' => ['required', 'integer', 'between:0,10000000'],
        ], [
            'prix.required' => 'Entre un prix.',
            'prix.integer' => 'Le prix doit être un nombre entier.',
            'prix.between' => 'Prix invalide.',
        ]);

        $plan->update([
            'prix' => $donnees['prix'],
            'actif' => $request->boolean('actif'),
        ]);

        return back()->with('succes', "Tarif « {$plan->nom} » mis à jour.");
    }
}