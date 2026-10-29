@extends('layouts.app')

@section('titre', 'Réussis ta licence')
@section('description', 'Cours en chapitres, annales corrigées, quiz et cours en direct pour les étudiants d\'économie, d\'industrie et d\'informatique au Congo.')

@section('contenu')
<section class="hero hero-accueil">
    <div class="conteneur hero-grille">
        <div>
            <h1>Réussis ta licence avec des cours faits pour ton programme</h1>
            <p class="lead">Cours en chapitres, annales corrigées, quiz et cours en direct. Pour les étudiants d'économie, d'industrie et d'informatique.</p>

            <form class="hero-recherche" action="{{ route('recherche') }}" method="GET" role="search">
                <input type="search" name="q" placeholder="Cherche une matière ou un chapitre…" aria-label="Rechercher">
                <button class="btn btn-primaire" type="submit">Rechercher</button>
            </form>

            <div class="hero-actions">
                <a class="btn btn-primaire btn-lg" href="{{ route('inscription') }}">Créer mon compte</a>
                <a class="btn btn-contour btn-lg" href="{{ route('aide') }}">Comment ça marche</a>
            </div>
        </div>

        {{-- Aperçu illustratif du produit : décoratif --}}
        <div class="scene" aria-hidden="true">
            <div class="p-cours">
                <div class="p-cours-video">
                    <span class="p-cours-lecture"><x-icone nom="lecture" :taille="30" /></span>
                </div>
                <small>Comptabilité générale · Chapitre 3</small>
                <strong>Lire un bilan comptable</strong>
                <div class="barre"><span style="width: 65%"></span></div>
                <small>Ta progression : 65 %</small>
            </div>

            <div class="p-code">
                <div class="barre-titre"><span class="point"></span><span class="point"></span><span class="point"></span><span>exercice.py</span></div>
<pre><span class="c-texte">nom</span> = input()
<span class="c-mot">print</span>(f"Bonjour, {nom} !")</pre>
                <span class="resultat-ok">✓ 3 tests réussis</span>
            </div>

            <div class="p-direct">
                <span class="etiquette"><span class="point-live"></span> En direct</span>
                <strong>Questions-réponses</strong>
                <small>Avec un mode audio qui économise ta connexion</small>
            </div>
        </div>
    </div>
</section>

@if ($universites->isNotEmpty())
    <div class="univers">
        <div class="conteneur">
            <span class="univers-titre">Disponible pour les étudiants de</span>
            <ul>
                @foreach ($universites as $u)
                    <li>{{ $u->sigle ?: $u->nom }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<section class="conteneur section">
    <div class="entete-section">
        <h2>Trois filières, chacune avec ses cours</h2>
        <p>Choisis la tienne : tu retrouves ensuite tes matières par niveau et par semestre.</p>
    </div>

    <div class="filieres-blocs">
        @foreach ($filieres as $filiere)
            @php($moins = $filiere->plans->sortBy('prix')->first())
            <article class="filiere-bloc" data-theme="{{ $filiere->theme }}">
                <span class="filiere-ico"><x-filiere-icone :theme="$filiere->theme" :taille="30" /></span>
                <h3>{{ $filiere->nom }}</h3>
                <p>{{ $filiere->description }}</p>

                @if ($moins)
                    <div class="prix-depuis">À partir de <strong>{{ number_format($moins->prix, 0, ',', ' ') }} FCFA</strong><br>formule {{ mb_strtolower($moins->nom) }}</div>
                @endif

                <div class="filiere-actions">
                    <a class="btn btn-primaire" href="{{ route('inscription', ['filiere' => $filiere->id]) }}">Choisir cette filière</a>
                    <a class="lien-clair" href="{{ route('filieres.show', $filiere->slug) }}">Voir les matières</a>
                </div>
            </article>
        @endforeach
    </div>
</section>

<section class="conteneur section">
    <div class="entete-section">
        <h2>Comment ça marche</h2>
    </div>
    <ol class="parcours">
        <li><h3>Crée ton compte</h3><p class="muted">Choisis ta filière, ton université et ton niveau.</p></li>
        <li><h3>Essaie gratuitement</h3><p class="muted">Lis les chapitres gratuits de chaque matière.</p></li>
        <li><h3>Abonne-toi</h3><p class="muted">Paie par Mobile Money, l'accès est activé aussitôt.</p></li>
        <li><h3>Réussis</h3><p class="muted">Cours, annales, quiz, direct et coach pour tes examens.</p></li>
    </ol>
</section>

<section class="conteneur section">
    <div class="entete-section">
        <h2>Tout pour réussir, au même endroit</h2>
    </div>
    <div class="atouts">
        <div class="atout"><span class="atout-ico"><x-icone nom="livre" :taille="22" /></span><h3>Cours en chapitres</h3><p>Vidéos, textes et PDF alignés sur ton programme, avec suivi de ta progression.</p></div>
        @if (Route::has('direct.index'))
            <div class="atout"><span class="atout-ico"><x-icone nom="video" :taille="22" /></span><h3>Cours en direct</h3><p>Des classes virtuelles avec tes enseignants, et un mode audio qui économise ta connexion.</p></div>
        @endif
        @if (Route::has('annales.index'))
            <div class="atout"><span class="atout-ico"><x-icone nom="fichier" :taille="22" /></span><h3>Annales corrigées</h3><p>Les sujets d'examens de ton université pour t'entraîner en conditions réelles.</p></div>
        @endif
        @if (Route::has('atelier.index'))
            <div class="atout"><span class="atout-ico"><x-icone nom="code" :taille="22" /></span><h3>Atelier de code</h3><p>Écris et teste du code directement dans ton navigateur : Python, JavaScript, SQL et HTML.</p></div>
        @endif
        @if (Route::has('classement'))
            <div class="atout"><span class="atout-ico"><x-icone nom="trophee" :taille="22" /></span><h3>Quiz et classement</h3><p>Teste-toi, gagne des points et fais monter ton université dans le classement.</p></div>
        @endif
        @if (Route::has('coach.index'))
            <div class="atout"><span class="atout-ico"><x-icone nom="message" :taille="22" /></span><h3>Coach IA</h3><p>Un tuteur disponible tout le temps : il explique, corrige tes exercices et t'interroge.</p></div>
        @endif
        @if (Route::has('parrainage'))
            <div class="atout"><span class="atout-ico"><x-icone nom="cadeau" :taille="22" /></span><h3>Parrainage</h3><p>Invite tes amis et gagne des jours d'abonnement gratuits.</p></div>
        @endif
    </div>
</section>

<section class="bande-cta">
    <div class="conteneur">
        <div>
            <h2>Prêt à commencer ?</h2>
            <p>Crée ton compte en une minute et essaie les chapitres gratuits.</p>
        </div>
        <a class="btn btn-primaire btn-lg" href="{{ route('inscription') }}">Créer mon compte</a>
    </div>
</section>
@endsection