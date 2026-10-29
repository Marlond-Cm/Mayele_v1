@extends('layouts.app')

@section('titre', 'Centre d\'aide')

@section('contenu')
<section class="hero hero-compact">
    <div class="conteneur">
        <span class="badge">Centre d'aide</span>
        <h1>Comment pouvons-nous t'aider ?</h1>
        <p class="lead">Tout comprendre sur {{ config('app.name') }}, de l'inscription aux quiz. Cherche une réponse ou pose ta question, l'assistant te répond tout de suite.</p>

        <div class="aide-recherche">
            <input type="search" id="aide-recherche" placeholder="Cherche : paiement, annale, parrainage…" autocomplete="off" aria-label="Rechercher dans l'aide">
            <button type="button" class="btn btn-primaire" data-ouvrir-aide>Poser ma question</button>
        </div>
    </div>
</section>

<div class="conteneur section">

    <h2 class="section-titre">{{ config('app.name') }} en 5 étapes</h2>
    <div class="aide-etapes">
        <div class="carte"><span class="etape-num">1</span><h3>Crée ton compte</h3><p class="muted">Choisis ta filière, ton université et ton niveau. C'est rapide et tu es connecté tout de suite.</p></div>
        <div class="carte"><span class="etape-num">2</span><h3>Explore le catalogue</h3><p class="muted">Parcours les matières et lis les chapitres gratuits pour essayer.</p></div>
        <div class="carte"><span class="etape-num">3</span><h3>Abonne-toi</h3><p class="muted">Paie par Mobile Money en quelques clics, ton accès est activé aussitôt.</p></div>
        <div class="carte"><span class="etape-num">4</span><h3>Apprends et t'entraîne</h3><p class="muted">Chapitres, annales corrigées, quiz et coach IA pour réussir tes examens.</p></div>
        <div class="carte"><span class="etape-num">5</span><h3>Progresse et gagne</h3><p class="muted">Monte dans le classement, parraine tes amis et dépose des annales pour gagner des jours gratuits.</p></div>
    </div>

    <h2 class="section-titre" style="margin-top:3rem">Les tarifs</h2>
    <div class="grille">
        @foreach ($filieres as $filiere)
            <article class="carte-filiere" data-theme="{{ $filiere->theme }}">
                <h3>{{ $filiere->nom }}</h3>
                <ul class="prix">
                    @foreach ($filiere->plans as $plan)
                        <li>
                            <span>{{ $plan->nom }} <small class="muted">({{ $plan->duree_jours }} j)</small></span>
                            <strong>{{ number_format($plan->prix, 0, ',', ' ') }} FCFA</strong>
                        </li>
                    @endforeach
                </ul>
                <a class="btn btn-contour" href="{{ route('filieres.show', $filiere->slug) }}">Voir les matières</a>
            </article>
        @endforeach
    </div>

    <h2 class="section-titre" style="margin-top:3rem">Toutes les réponses</h2>

    <p id="aide-aucun" class="carte" hidden>
        Aucun article ne correspond à ta recherche.
        <button type="button" class="btn btn-primaire btn-sm" data-ouvrir-aide>Demander à l'assistant</button>
    </p>

    <div id="aide-categories">
        @foreach ($categories as $categorie)
            <section class="aide-categorie" id="{{ $categorie['slug'] }}">
                <h2>{{ $categorie['icone'] }} {{ $categorie['titre'] }}</h2>

                @foreach ($categorie['articles'] as $article)
                    <details class="aide-article" id="{{ $article['slug'] }}"
                             data-texte="{{ Str::lower(Str::ascii($article['question'].' '.$article['reponse'])) }}">
                        <summary>{{ $article['question'] }}</summary>
                        <div class="contenu">{!! Str::markdown($article['reponse'], ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
                    </details>
                @endforeach
            </section>
        @endforeach
    </div>

    <section class="carte aide-contact" id="support">
        <h2>Besoin d'autre chose ?</h2>
        <p class="muted">Pose ta question à l'assistant, ou écris à l'équipe. Pour aller plus vite, indique l'e-mail de ton inscription et décris le problème. Pour un paiement, ajoute la référence (MAY-…) et le numéro utilisé.</p>
        <div class="actions-chapitre" style="margin-bottom:0">
            <button type="button" class="btn btn-primaire" data-ouvrir-aide>💬 Poser une question</button>
            @if ($lienSupport)
                <a class="btn btn-contour" href="{{ $lienSupport }}" target="_blank" rel="noopener">Contacter le support sur WhatsApp</a>
            @endif
            @if ($emailSupport)
                <a class="btn btn-contour" href="mailto:{{ $emailSupport }}">{{ $emailSupport }}</a>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    const normaliser = (texte) => texte.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    const recherche = document.getElementById('aide-recherche');
    const articles = document.querySelectorAll('.aide-article');
    const categories = document.querySelectorAll('.aide-categorie');
    const aucun = document.getElementById('aide-aucun');

    recherche.addEventListener('input', () => {
        const mots = normaliser(recherche.value).split(/\s+/).filter((m) => m.length >= 2);
        let trouves = 0;

        articles.forEach((article) => {
            const visible = mots.every((mot) => article.dataset.texte.includes(mot));
            article.hidden = !visible;
            article.open = visible && mots.length > 0;
            if (visible) trouves++;
        });

        categories.forEach((categorie) => {
            categorie.hidden = !categorie.querySelector('.aide-article:not([hidden])');
        });

        aucun.hidden = trouves > 0 || mots.length === 0;
    });

    function ouvrirCible() {
        const cible = location.hash ? document.getElementById(location.hash.slice(1)) : null;
        if (cible && cible.tagName === 'DETAILS') {
            cible.open = true;
            cible.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    window.addEventListener('hashchange', ouvrirCible);
    ouvrirCible();
</script>
@endpush