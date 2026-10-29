<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AbonnementController;
use App\Http\Controllers\AccueilController;
use App\Http\Controllers\AnnaleController;
use App\Http\Controllers\Auth\ConnexionController;
use App\Http\Controllers\Auth\DeconnexionController;
use App\Http\Controllers\Auth\InscriptionController;
use App\Http\Controllers\ChapitreController;
use App\Http\Controllers\ClassementController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\FiliereController;
use App\Http\Controllers\MatiereController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\ParrainageController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\TableauDeBordController;
use Illuminate\Support\Facades\Route;

Route::get('/', AccueilController::class)->name('accueil');

// Catalogue (public : les chapitres gratuits servent d'aperçu aux visiteurs)
Route::get('/filieres/{filiere:slug}', [FiliereController::class, 'show'])->name('filieres.show');
Route::get('/matieres/{matiere:slug}', [MatiereController::class, 'show'])->name('matieres.show');
Route::get('/matieres/{matiere:slug}/chapitres/{chapitre:slug}', [ChapitreController::class, 'show'])->name('chapitres.show');
Route::get('/chapitres/{chapitre}/pdf', [ChapitreController::class, 'pdf'])->name('chapitres.pdf');

// Annales : la liste est publique, les fichiers sont réservés aux abonnés
Route::get('/annales', [AnnaleController::class, 'index'])->name('annales.index');

// Quiz et classement (publics en lecture ; passer un quiz demande un compte)
Route::get('/quiz/{quiz}', [QuizController::class, 'show'])->name('quiz.show');
Route::get('/classement', ClassementController::class)->name('classement');

Route::middleware('guest')->group(function () {
    Route::get('/inscription', [InscriptionController::class, 'create'])->name('inscription');
    Route::post('/inscription', [InscriptionController::class, 'store']);
    Route::get('/connexion', [ConnexionController::class, 'create'])->name('connexion');
    Route::post('/connexion', [ConnexionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', DeconnexionController::class)->name('deconnexion');
    Route::get('/tableau-de-bord', TableauDeBordController::class)->name('tableau-de-bord');
    Route::post('/chapitres/{chapitre}/terminer', [ChapitreController::class, 'terminer'])->name('chapitres.terminer');

    Route::get('/abonnement', [AbonnementController::class, 'index'])->name('abonnement.index');
    Route::post('/abonnement/payer', [AbonnementController::class, 'payer'])->name('abonnement.payer');
    Route::get('/paiements/{paiement:reference}', [PaiementController::class, 'retour'])->name('paiements.retour');

    Route::get('/annales/deposer', [AnnaleController::class, 'deposer'])->name('annales.deposer');
    Route::post('/annales/deposer', [AnnaleController::class, 'stocker'])->name('annales.stocker');
    Route::get('/mes-annales', [AnnaleController::class, 'mesDepots'])->name('annales.mes-depots');
    Route::get('/annales/{annale}/fichier/{type}', [AnnaleController::class, 'fichier'])
        ->whereIn('type', ['sujet', 'corrige'])->name('annales.fichier');

    Route::get('/parrainage', ParrainageController::class)->name('parrainage');

    Route::post('/quiz/{quiz}', [QuizController::class, 'soumettre'])->middleware('throttle:30,1')->name('quiz.soumettre');
    Route::get('/quiz-resultat/{tentative}', [QuizController::class, 'resultat'])->name('quiz.resultat');

    // Coach IA
    Route::get('/coach', [CoachController::class, 'index'])->name('coach.index');
    Route::post('/coach/demarrer', [CoachController::class, 'demarrer'])->name('coach.demarrer');
    Route::get('/coach/{conversation}', [CoachController::class, 'show'])->name('coach.show');
    Route::post('/coach/{conversation}/envoyer', [CoachController::class, 'envoyer'])->middleware('throttle:12,1')->name('coach.envoyer');
    Route::delete('/coach/{conversation}', [CoachController::class, 'destroy'])->name('coach.destroy');
});

// ================= Administration =================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', Admin\TableauDeBordController::class)->name('tableau');

    Route::resource('matieres', Admin\MatiereController::class)
        ->except('show')->parameters(['matieres' => 'matiere']);

    Route::resource('matieres.chapitres', Admin\ChapitreController::class)
        ->shallow()->except('show')->parameters(['matieres' => 'matiere', 'chapitres' => 'chapitre']);

    Route::post('quiz/generer', [Admin\QuizController::class, 'generer'])->middleware('throttle:10,1')->name('quiz.generer');
    Route::resource('quiz', Admin\QuizController::class)->except('show');

    Route::resource('universites', Admin\UniversiteController::class)
        ->except('show')->parameters(['universites' => 'universite']);

    Route::resource('annales', Admin\AnnaleController::class)
        ->except('show')->parameters(['annales' => 'annale']);
    Route::post('annales/{annale}/valider', [Admin\AnnaleController::class, 'valider'])->name('annales.valider');
    Route::post('annales/{annale}/refuser', [Admin\AnnaleController::class, 'refuser'])->name('annales.refuser');
    Route::get('annales/{annale}/fichier/{type}', [Admin\AnnaleController::class, 'fichier'])
        ->whereIn('type', ['sujet', 'corrige'])->name('annales.fichier');

    Route::get('plans', [Admin\PlanController::class, 'index'])->name('plans.index');
    Route::put('plans/{plan}', [Admin\PlanController::class, 'update'])->name('plans.update');

    Route::get('paiements', [Admin\PaiementController::class, 'index'])->name('paiements.index');
    Route::post('paiements/{paiement}/verifier', [Admin\PaiementController::class, 'verifier'])->name('paiements.verifier');

    Route::get('utilisateurs', [Admin\UtilisateurController::class, 'index'])->name('utilisateurs.index');
    Route::get('utilisateurs/{user}', [Admin\UtilisateurController::class, 'show'])->name('utilisateurs.show');
    Route::post('utilisateurs/{user}/abonnement', [Admin\UtilisateurController::class, 'offrir'])->name('utilisateurs.offrir');

    Route::get('coach', Admin\CoachController::class)->name('coach');
});

// ================= Aide =================
Route::get('/aide', [\App\Http\Controllers\AideController::class, 'index'])->name('aide');
Route::post('/aide/question', [\App\Http\Controllers\AideController::class, 'question'])
    ->middleware('throttle:15,1')->name('aide.question');
Route::post('/aide/reinitialiser', [\App\Http\Controllers\AideController::class, 'reinitialiser'])
    ->middleware('throttle:15,1')->name('aide.reinitialiser');
Route::post('/aide/avis/{question}', [\App\Http\Controllers\AideController::class, 'avis'])
    ->middleware('throttle:30,1')->name('aide.avis');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('aide', \App\Http\Controllers\Admin\AideController::class)->name('aide');
});

// ================= Cours en direct =================
Route::get('/direct', [\App\Http\Controllers\DirectController::class, 'index'])->name('direct.index');
Route::get('/direct/{classe}', [\App\Http\Controllers\DirectController::class, 'show'])->name('direct.show')->whereNumber('classe');
Route::get('/direct/{classe}/calendrier', [\App\Http\Controllers\DirectController::class, 'calendrier'])->name('direct.calendrier')->whereNumber('classe');

Route::middleware('auth')->group(function () {
    Route::get('/direct/{classe}/salle', [\App\Http\Controllers\DirectController::class, 'salle'])->name('direct.salle')->whereNumber('classe');
    Route::post('/direct/{classe}/presence', [\App\Http\Controllers\DirectController::class, 'presence'])
        ->middleware('throttle:6,1')->name('direct.presence')->whereNumber('classe');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::resource('direct', \App\Http\Controllers\Admin\DirectController::class)
        ->except('show')->parameters(['direct' => 'classe']);
    Route::get('direct/{classe}/presences', [\App\Http\Controllers\Admin\DirectController::class, 'presences'])
        ->name('direct.presences');
});

Route::get('/recherche', \App\Http\Controllers\RechercheController::class)->name('recherche');

// ================= Atelier de code et nouveautés =================
Route::get('/atelier', [\App\Http\Controllers\AtelierController::class, 'index'])->name('atelier.index');

Route::middleware('auth')->group(function () {
    Route::get('/atelier/{exercice:slug}', [\App\Http\Controllers\AtelierController::class, 'show'])->name('atelier.show');
    Route::post('/atelier/{exercice:slug}/valider', [\App\Http\Controllers\AtelierController::class, 'valider'])
        ->middleware('throttle:40,1')->name('atelier.valider');
    Route::post('/atelier/{exercice:slug}/solution', [\App\Http\Controllers\AtelierController::class, 'solution'])
        ->middleware('throttle:20,1')->name('atelier.solution');
    Route::get('/nouveautes', \App\Http\Controllers\NouveautesController::class)->name('nouveautes');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::resource('exercices', \App\Http\Controllers\Admin\ExerciceController::class)
        ->except('show')->parameters(['exercices' => 'exercice']);
});

// ================= Mot de passe oublié =================
Route::middleware('guest')->group(function () {
    Route::get('/mot-de-passe-oublie', [\App\Http\Controllers\MotDePasseController::class, 'demande'])->name('mot-de-passe.oublie');
    Route::post('/mot-de-passe-oublie', [\App\Http\Controllers\MotDePasseController::class, 'envoyer'])
        ->middleware('throttle:5,1')->name('mot-de-passe.envoyer');
    Route::get('/reinitialiser-mot-de-passe/{token}', [\App\Http\Controllers\MotDePasseController::class, 'formulaire'])
        ->name('mot-de-passe.reinitialiser');
    Route::post('/reinitialiser-mot-de-passe', [\App\Http\Controllers\MotDePasseController::class, 'reinitialiser'])
        ->middleware('throttle:10,1')->name('mot-de-passe.mettre-a-jour');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('surveillance', [\App\Http\Controllers\Admin\SurveillanceController::class, 'index'])->name('surveillance');
    Route::post('surveillance/verifier', [\App\Http\Controllers\Admin\SurveillanceController::class, 'verifier'])->name('surveillance.verifier');
});