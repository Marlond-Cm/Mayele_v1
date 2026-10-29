@extends('layouts.app')

@section('titre', 'Mon espace')

@section('contenu')
@php($icones = ['economie' => '📊', 'industrie' => '⚙️', 'informatique' => '💻'])
<div class="conteneur section">
    <div class="accueil-entete">
        <div>
            <p class="muted" style="margin:0">
                {{ collect([$user->universite?->nom, $filiere?->nom, $user->niveau])->filter()->implode(' · ') }}
            </p>
            <h1>Bonjour {{ strtok($user->name, ' ') }} 👋</h1>
        </div>
        @if ($filiere)
            <a class="btn btn-contour" href="{{ route('filieres.show', $filiere->slug) }}">Explorer le catalogue</a>
        @endif
    </div>

    @if ($filiere === null)
        <div class="alerte">Ton compte n'est rattaché à aucune filière.</div>
    @else
        <div class="stats">
            <div class="carte stat"><small class="muted">Chapitres terminés</small><strong>{{ $nbTermines }}</strong></div>
            <div class="carte stat"><small class="muted">Matières en cours</small><strong>{{ $enCours->count() }}</strong></div>
            @if ($points !== null)
                <div class="carte stat"><small class="muted">Points aux quiz</small><strong>{{ number_format($points, 0, ',', ' ') }}</strong></div>
            @endif
            <a class="carte stat lien-carte" href="{{ route('abonnement.index') }}">
                <small class="muted">Abonnement</small>
                <strong>{{ $abonnement ? $joursRestants.' j' : '—' }}</strong>
                <small>{{ $abonnement ? 'restants · '.$abonnement->plan->nom : 'Aucun abonnement actif' }}</small>
            </a>
        </div>

        <h2>Reprendre là où tu t'es arrêté</h2>
        @if ($enCours->isNotEmpty())
            <div class="grille-reprise">
                @foreach ($enCours as $item)
                    @php($pct = (int) round($item['fait'] / $item['total'] * 100))
                    <div class="carte reprise" data-theme="{{ $filiere->theme }}">
                    <span class="reprise-vignette"><x-filiere-icone :theme="$filiere->theme" :taille="32" /></span>
                        <div style="flex:1;min-width:0">
                            <h3>{{ $item['matiere']->nom }}</h3>
                            <div class="barre"><span @style(['width' => $pct . '%'])></span></div>
                            <small class="muted">{{ $item['fait'] }} / {{ $item['total'] }} chapitres · {{ $pct }} %</small>
                            <div style="margin-top:.6rem">
                                @if ($item['suivant'])
                                    <a class="btn btn-primaire btn-sm" href="{{ route('chapitres.show', [$item['matiere']->slug, $item['suivant']->slug]) }}">Reprendre : {{ \Illuminate\Support\Str::limit($item['suivant']->titre, 28) }}</a>
                                @else
                                    <a class="btn btn-primaire btn-sm" href="{{ route('matieres.show', $item['matiere']->slug) }}">Voir la matière</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="carte vide-etat">
                <h3>Commence ton parcours</h3>
                <p class="muted">Ouvre une matière et lis son premier chapitre : ta progression apparaîtra ici.</p>
                <a class="btn btn-primaire" href="{{ route('filieres.show', $filiere->slug) }}">Voir les matières</a>
            </div>
        @endif

        <div class="deux-colonnes">
            <div class="carte">
                <h3>Mon abonnement</h3>
                @if ($abonnement)
                    <span class="badge badge-ok">Actif</span>
                    <p>Formule {{ $abonnement->plan->nom }}, valable jusqu'au {{ $abonnement->fin_le->translatedFormat('d F Y') }}.</p>
                    <a class="btn btn-contour btn-sm" href="{{ route('abonnement.index') }}">Prolonger</a>
                @else
                    <span class="badge badge-off">Aucun abonnement actif</span>
                    <p class="muted">Débloque tous les chapitres, les annales, les quiz et le coach.</p>
                    <ul class="prix">
                        @foreach ($plans as $plan)
                            <li><span>{{ $plan->nom }}</span><strong>{{ number_format($plan->prix, 0, ',', ' ') }} FCFA</strong></li>
                        @endforeach
                    </ul>
                    <a class="btn btn-primaire btn-sm" href="{{ route('abonnement.index') }}">S'abonner</a>
                @endif
            </div>

            <div class="carte">
                <h3>Cours en direct</h3>
                @if ($prochainDirect)
                    <a class="direct-prochain carte lien-carte" href="{{ route('direct.show', $prochainDirect) }}">
                        <small class="muted">Prochain cours</small>
                        <h3 style="margin:.2rem 0">{{ $prochainDirect->titre }}</h3>
                        <p class="muted" style="margin:0">{{ $prochainDirect->debut_le->translatedFormat('l d F à H\hi') }} · {{ $prochainDirect->libelleEtat() }}</p>
                    </a>
                @else
                    <p class="muted">Aucun cours en direct prévu pour le moment.</p>
                @endif
                @if (Route::has('direct.index'))
                    <a class="btn btn-contour btn-sm" style="margin-top:.8rem" href="{{ route('direct.index') }}">Tous les cours en direct</a>
                @endif
            </div>
        </div>

        <h2>Mes matières</h2>
        @forelse ($matieres as $semestre => $liste)
            <h3 class="semestre-titre">Semestre {{ $semestre }}</h3>
            <div class="grille-cours">
                @foreach ($liste as $matiere)
                    <x-matiere-carte :matiere="$matiere" :theme="$filiere->theme" :total="$matiere->chapitres_total" :fait="(int) ($termines[$matiere->id]->total ?? 0)" />
                @endforeach
            </div>
        @empty
            <p class="muted">Aucune matière disponible pour ton niveau pour le moment.</p>
        @endforelse
    @endif
</div>
@endsection