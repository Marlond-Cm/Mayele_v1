@extends('layouts.app')

@section('titre', $matiere->nom)

@section('contenu')
@php
    $icones = ['economie' => '📊', 'industrie' => '⚙️', 'informatique' => '💻'];
    $prochain = $chapitres->first(fn ($c) => ! in_array($c->id, $termines, true)) ?? $chapitres->first();
    $quizzes = \App\Models\Quiz::where('matiere_id', $matiere->id)->where('publie', true)->has('questions')->withCount('questions')->get();
@endphp

<section class="hero hero-compact">
    <div class="conteneur">
        <p class="fil-ariane" style="color:rgba(255,255,255,.8)">
            <a href="{{ route('filieres.show', $matiere->filiere->slug) }}">{{ $matiere->filiere->nom }}</a> › {{ $matiere->niveau }} · Semestre {{ $matiere->semestre }}
        </p>
        <h1>{{ $matiere->nom }}</h1>
        @if ($matiere->parcours)<span class="badge">{{ $matiere->parcours }}</span>@endif
        @if ($matiere->description)<p class="lead">{{ $matiere->description }}</p>@endif
        <div class="chips-info">
            <span class="chip-info">{{ $chapitres->count() }} chapitre{{ $chapitres->count() > 1 ? 's' : '' }}</span>
            @if ($chapitres->sum('duree_minutes') > 0)<span class="chip-info">≈ {{ $chapitres->sum('duree_minutes') }} min</span>@endif
            @if ($quizzes->isNotEmpty())<span class="chip-info">{{ $quizzes->count() }} quiz</span>@endif
        </div>
        @if ($prochain)
            <a class="btn btn-primaire btn-lg" href="{{ route('chapitres.show', [$matiere->slug, $prochain->slug]) }}">
                <x-icone nom="lecture" :taille="20" /> {{ count($termines) > 0 ? 'Continuer' : 'Commencer' }}
            </a>
        @endif
    </div>
</section>

<div class="conteneur section">
    <div class="cours-disposition">
        <div>
            <h2>Programme du cours</h2>

            @if ($chapitres->isEmpty())
                <div class="carte vide-etat"><p class="muted" style="margin:0">Les chapitres de cette matière arrivent bientôt.</p></div>
            @else
                <ol class="liste-chapitres">
                    @foreach ($chapitres as $chapitre)
                        @php($fait = in_array($chapitre->id, $termines, true))
                        <li>
                            <a class="ligne-chapitre {{ $fait ? 'fait' : '' }}" href="{{ route('chapitres.show', [$matiere->slug, $chapitre->slug]) }}">
                                <span class="num">{{ $fait ? '✓' : $loop->iteration }}</span>
                                <span class="titre">{{ $chapitre->titre }}</span>
                                @if ($chapitre->est_gratuit)
                                    <span class="badge badge-gratuit" style="margin:0">Gratuit</span>
                                @elseif (! $abonne)
                                    <span class="meta">🔒</span>
                                @endif
                                @if ($chapitre->duree_minutes > 0)<span class="meta">{{ $chapitre->duree_minutes }} min</span>@endif
                            </a>
                        </li>
                    @endforeach
                </ol>
            @endif

            @if ($quizzes->isNotEmpty())
                <h2 class="semestre-titre" style="margin-top:2.2rem">Quiz</h2>
                <div class="grille">
                    @foreach ($quizzes as $quiz)
                        <a class="carte lien-carte" href="{{ route('quiz.show', $quiz) }}">
                            <h3>{{ $quiz->titre }}</h3>
                            <small class="muted">{{ $quiz->questions_count }} questions{{ $quiz->est_gratuit ? ' · Gratuit' : '' }}</small>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <aside class="panneau-lateral">
            @auth
                <div class="carte">
                    <strong>Ta progression : {{ $pourcentage }} %</strong>
                    <div class="barre"><span @style(['width' => $pourcentage . '%'])></span></div>
                    <small class="muted">{{ count($termines) }} / {{ $chapitres->count() }} chapitres terminés</small>
                </div>
            @endauth

            @if (! $abonne)
                <div class="carte">
                    <h3>Accès complet</h3>
                    <p class="muted">Abonne-toi à {{ $matiere->filiere->nom }} pour débloquer tous les chapitres, les annales et le coach.</p>
                    <a class="btn btn-primaire btn-bloc" href="{{ auth()->check() ? route('abonnement.index', ['filiere' => $matiere->filiere->slug]) : route('inscription', ['filiere' => $matiere->filiere_id]) }}">
                        {{ auth()->check() ? 'S\'abonner' : 'Créer mon compte' }}
                    </a>
                </div>
            @endif

            <div class="carte">
                <h3>Pour réussir</h3>
                <div style="display:grid;gap:.5rem">
                    @if (Route::has('annales.index'))
                        <a class="btn btn-contour btn-bloc" href="{{ route('annales.index', ['matiere' => $matiere->slug]) }}">
                            <x-icone nom="fichier" :taille="18" /> Annales ({{ $matiere->annales()->valide()->count() }})
                        </a>
                    @endif
                    @if (Route::has('direct.index'))
                        <a class="btn btn-contour btn-bloc" href="{{ route('direct.index', ['filiere' => $matiere->filiere->slug]) }}">
                            <x-icone nom="video" :taille="18" /> Cours en direct
                        </a>
                    @endif
                    @if (Route::has('coach.demarrer'))
                        @auth
                            <form method="POST" action="{{ route('coach.demarrer') }}" style="margin:0">
                                @csrf
                                <input type="hidden" name="matiere_id" value="{{ $matiere->id }}">
                                <input type="hidden" name="mode" value="libre">
                                <button type="submit" class="btn btn-contour btn-bloc"><x-icone nom="message" :taille="18" /> Poser une question au coach</button>
                            </form>
                        @endauth
                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection