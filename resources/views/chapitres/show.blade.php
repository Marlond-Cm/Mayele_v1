@extends('layouts.app')

@section('titre', $chapitre->titre)

@section('contenu')
@php
    $syllabus = $matiere->chapitres()->where('publie', true)->get();
    $faits = auth()->check()
        ? \App\Models\Progression::where('user_id', auth()->id())->whereNotNull('termine_le')->whereIn('chapitre_id', $syllabus->pluck('id'))->pluck('chapitre_id')->all()
        : [];
    $abonne = auth()->check() && auth()->user()->aAbonnementActifPour($matiere->filiere_id);
    $video = $accessible ? $chapitre->lecteurVideo() : null;
@endphp

<div class="conteneur section">
    <p class="fil-ariane">
        <a href="{{ route('filieres.show', $matiere->filiere->slug) }}">{{ $matiere->filiere->nom }}</a> ›
        <a href="{{ route('matieres.show', $matiere->slug) }}">{{ $matiere->nom }}</a> › {{ $chapitre->titre }}
    </p>

    <div class="lecteur">
        <div>
            <h1 style="font-size:1.9rem">{{ $chapitre->titre }}</h1>

            @if ($accessible)
                @if ($video)
                    <div class="cadre-video">
                        @if ($video['type'] === 'youtube')
                            <iframe src="{{ $video['url'] }}" title="{{ $chapitre->titre }}" allow="encrypted-media; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                        @else
                            <video controls preload="metadata" src="{{ $video['url'] }}"></video>
                        @endif
                    </div>
                @endif

                @if ($contenuHtml)
                    <div class="contenu carte">{!! $contenuHtml !!}</div>
                @endif

                <div class="actions-chapitre">
                    @if ($chapitre->fichier_pdf)
                        <a class="btn btn-contour" href="{{ route('chapitres.pdf', $chapitre->id) }}"><x-icone nom="fichier" :taille="18" /> Télécharger le PDF</a>
                    @endif

                    @auth
                        @if ($termine)
                            <span class="badge badge-ok" style="margin:0">✓ Chapitre terminé</span>
                        @else
                            <form method="POST" action="{{ route('chapitres.terminer', $chapitre->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-primaire">Marquer comme terminé</button>
                            </form>
                        @endif
                    @else
                        <span class="muted"><a href="{{ route('connexion') }}">Connecte-toi</a> pour suivre ta progression.</span>
                    @endauth
                </div>

                @auth
                    @if (Route::has('coach.demarrer'))
                        <form class="actions-chapitre" method="POST" action="{{ route('coach.demarrer') }}">
                            @csrf
                            <input type="hidden" name="chapitre_id" value="{{ $chapitre->id }}">
                            <span class="muted"><x-icone nom="message" :taille="18" /> Coach IA :</span>
                            <button type="submit" name="mode" value="expliquer" class="btn btn-contour btn-sm">Expliquer</button>
                            <button type="submit" name="mode" value="corriger" class="btn btn-contour btn-sm">Corriger mon exercice</button>
                            <button type="submit" name="mode" value="interroger" class="btn btn-contour btn-sm">M'interroger</button>
                        </form>
                    @endif
                @endauth

                @php($exercicesLies = class_exists(\App\Models\Exercice::class) ? \App\Models\Exercice::where('publie', true)->where('chapitre_id', $chapitre->id)->orderBy('ordre')->get() : collect())
                @if ($exercicesLies->isNotEmpty())
                    <div class="carte" style="margin-top:1.2rem">
                        <h3>💻 Pratique ce chapitre</h3>
                        <div style="display:grid;gap:.5rem">
                            @foreach ($exercicesLies as $exo)
                                <a class="btn btn-contour" href="{{ route('atelier.show', $exo->slug) }}">{{ $exo->titre }} · {{ \App\Models\Exercice::LANGAGES[$exo->langage] }}</a>
                            @endforeach
                        </div>
                </div>
                @endif

                <div class="nav-chapitre">
                    @if ($precedent)
                        <a class="btn btn-contour" href="{{ route('chapitres.show', [$matiere->slug, $precedent->slug]) }}">← Précédent</a>
                    @else
                        <span></span>
                    @endif
                    @if ($suivant)
                        <a class="btn btn-primaire" href="{{ route('chapitres.show', [$matiere->slug, $suivant->slug]) }}">Suivant →</a>
                    @endif
                </div>
            @else
                <div class="carte verrou">
                    <div class="icone-grand">🔒</div>
                    <h2>Chapitre réservé aux abonnés</h2>
                    <p class="muted">Débloque tous les chapitres de {{ $matiere->filiere->nom }}.</p>
                    <ul class="prix" style="max-width:380px;margin-inline:auto">
                        @foreach ($plans as $plan)
                            <li><span>{{ $plan->nom }}</span><strong>{{ number_format($plan->prix, 0, ',', ' ') }} FCFA</strong></li>
                        @endforeach
                    </ul>
                    @guest
                        <a class="btn btn-primaire" href="{{ route('inscription', ['filiere' => $matiere->filiere_id]) }}">Créer mon compte</a>
                    @endguest
                    @auth
                        <a class="btn btn-primaire" href="{{ route('abonnement.index', ['filiere' => $matiere->filiere->slug]) }}">S'abonner</a>
                    @endauth
                </div>
            @endif
        </div>

        <aside class="panneau-lateral">
            <div class="carte">
                <h3>Contenu du cours</h3>
                <p class="muted" style="margin-bottom:.8rem"><small>{{ $matiere->nom }}</small></p>
                <ol class="liste-chapitres">
                    @foreach ($syllabus as $c)
                        @php($fait = in_array($c->id, $faits, true))
                        <li>
                            <a class="ligne-chapitre {{ $c->id === $chapitre->id ? 'courant' : '' }} {{ $fait ? 'fait' : '' }}"
                               href="{{ route('chapitres.show', [$matiere->slug, $c->slug]) }}">
                                <span class="num">{{ $fait ? '✓' : $loop->iteration }}</span>
                                <span class="titre">{{ $c->titre }}</span>
                                @if (! $c->est_gratuit && ! $abonne)<span class="meta">🔒</span>@endif
                            </a>
                        </li>
                    @endforeach
                </ol>
            </div>
        </aside>
    </div>
</div>
@endsection