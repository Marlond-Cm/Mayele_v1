<?php

namespace App\Http\Controllers;

use App\Models\Filiere;
use App\Models\Universite;
use App\Models\User;
use App\Services\ClassementService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassementController extends Controller
{
    public function __invoke(Request $request, ClassementService $service): View
    {
        $periode = in_array($request->query('periode'), ['semaine', 'mois', 'tout'], true)
            ? $request->query('periode')
            : 'semaine';

        $filiere = $request->filled('filiere')
            ? Filiere::where('slug', $request->query('filiere'))->where('actif', true)->first()
            : null;

        $points = $service->points($filiere?->id, $periode);

        // Top étudiants (noms abrégés)
        $top = $points->take(20);
        $users = User::with('universite')->whereIn('id', $top->keys())->get()->keyBy('id');
        $moi = $request->user();

        $etudiants = $top->map(fn ($p, $id) => [
            'nom' => ClassementService::nomPublic($users[$id]->name),
            'universite' => $users[$id]->universite?->sigle ?: $users[$id]->universite?->nom,
            'points' => $p,
            'moi' => $moi && $moi->id == $id,
        ])->values();

        // Classement des universités
        $classementUnis = $service->universites($points);
        $unis = Universite::whereIn('id', $classementUnis->keys()->take(10))->get()->keyBy('id');

        $universites = $classementUnis->take(10)->map(fn ($donnees, $id) => [
            'nom' => $unis[$id]->nom,
            'total' => $donnees['total'],
            'participants' => $donnees['participants'],
        ])->values();

        // Mon rang
        $position = $moi ? $points->keys()->search($moi->id) : false;

        return view('classement.index', [
            'periode' => $periode,
            'filiere' => $filiere,
            'filieres' => Filiere::where('actif', true)->orderBy('ordre')->get(),
            'etudiants' => $etudiants,
            'universites' => $universites,
            'monRang' => $position === false ? null : $position + 1,
            'mesPoints' => $moi ? ($points[$moi->id] ?? 0) : 0,
            'theme' => $filiere?->theme,
        ]);
    }
}