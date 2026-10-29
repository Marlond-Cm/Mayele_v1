@php
    $theme = $theme ?? auth()->user()?->filiere?->theme ?? 'mayele';
    $utilisateur = auth()->user();
    $navFilieres = \App\Models\Filiere::where('actif', true)->orderBy('ordre')->get(['id', 'nom', 'slug', 'theme']);
    $support = class_exists(\App\Services\AideService::class) ? app(\App\Services\AideService::class)->lienSupport() : null;
    $aboActif = $utilisateur ? $utilisateur->abonnements()->actif()->orderByDesc('fin_le')->first() : null;
@endphp
<!DOCTYPE html>
<html lang="fr" data-theme="{{ $theme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre', 'Accueil') · {{ config('app.name') }}</title>
    @include('partials.head-marque')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="{{ $utilisateur ? 'avec-menu' : 'public' }}">

@auth
    <div class="app">
        <aside class="side" id="side">
            <a class="logo" href="{{ route('tableau-de-bord') }}" aria-label="{{ config('app.name') }} : mon espace"><x-logo /></a>

            <div class="side-groupe">
                <span class="side-titre">Apprendre</span>
                <a href="{{ route('tableau-de-bord') }}" class="{{ request()->routeIs('tableau-de-bord') ? 'actif' : '' }}"><x-icone nom="home" /> Mon espace</a>
                <a href="{{ route('filieres.show', ($utilisateur->filiere ?? $navFilieres->first())->slug) }}"
                   class="{{ request()->routeIs('filieres.*', 'matieres.*', 'chapitres.*', 'recherche') ? 'actif' : '' }}"><x-icone nom="livre" /> Catalogue</a>
                @if (Route::has('direct.index'))
                    <a href="{{ route('direct.index') }}" class="{{ request()->routeIs('direct.*') ? 'actif' : '' }}"><x-icone nom="video" /> Cours en direct</a>
                @endif
                @if (Route::has('annales.index'))
                    <a href="{{ route('annales.index') }}" class="{{ request()->routeIs('annales.*') ? 'actif' : '' }}"><x-icone nom="fichier" /> Annales</a>
                @endif
                @if (Route::has('atelier.index'))
                    <a href="{{ route('atelier.index') }}" class="{{ request()->routeIs('atelier.*') ? 'actif' : '' }}"><x-icone nom="code" /> Atelier de code</a>
                @endif
                @if (Route::has('classement'))
                    <a href="{{ route('classement') }}" class="{{ request()->routeIs('classement', 'quiz.*') ? 'actif' : '' }}"><x-icone nom="trophee" /> Quiz et classement</a>
                @endif
            </div>

            <div class="side-groupe">
                <span class="side-titre">Progresser</span>
                @if (Route::has('coach.index'))
                    <a href="{{ route('coach.index') }}" class="{{ request()->routeIs('coach.*') ? 'actif' : '' }}"><x-icone nom="message" /> Coach IA</a>
                @endif
                @if (Route::has('parrainage'))
                    <a href="{{ route('parrainage') }}" class="{{ request()->routeIs('parrainage') ? 'actif' : '' }}"><x-icone nom="cadeau" /> Parrainage</a>
                @endif
            </div>

            <div class="side-groupe side-bas">
                <span class="side-titre">Compte</span>
                <a href="{{ route('abonnement.index') }}" class="{{ request()->routeIs('abonnement.*', 'paiements.*') ? 'actif' : '' }}"><x-icone nom="carte" /> Abonnement</a>
                <a href="{{ route('aide') }}" class="{{ request()->routeIs('aide') ? 'actif' : '' }}"><x-icone nom="aide" /> Aide</a>
                @if ($utilisateur->estAdmin())
                    <a href="{{ route('admin.tableau') }}"><x-icone nom="bouclier" /> Administration</a>
                @endif
            </div>
        </aside>
        <div class="voile" id="voile"></div>

        <div class="app-corps">
            <header class="topbar">
                <button type="button" class="side-burger" id="side-burger" aria-label="Ouvrir le menu"><x-icone nom="menu" /></button>

                <form class="recherche-barre" action="{{ route('recherche') }}" method="GET" role="search">
                    <x-icone nom="recherche" :taille="18" />
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher une matière ou un chapitre…" aria-label="Rechercher">
                </form>

                <div class="topbar-droite">
                    @if (Route::has('nouveautes'))
                        @php($nbNouveautes = \App\Models\Nouveaute::nonLuesPour($utilisateur))
                        <a class="nav-icone cloche" href="{{ route('nouveautes') }}" aria-label="Nouveautés">
                            <x-icone nom="cloche" />
                            @if ($nbNouveautes > 0)<span class="cloche-nb">{{ $nbNouveautes > 9 ? '9+' : $nbNouveautes }}</span>@endif
                        </a>
                    @endif

                    <x-bascule-mode />

                    @if ($aboActif)
                        <a class="pastille-abo oui" href="{{ route('abonnement.index') }}">✓ Abonné jusqu'au {{ $aboActif->fin_le->translatedFormat('d/m') }}</a>
                    @else
                        <a class="pastille-abo non" href="{{ route('abonnement.index') }}">S'abonner</a>
                    @endif

                    <details class="menu-deroulant profil">
                        <summary>
                            <span class="avatar">{{ mb_strtoupper(mb_substr($utilisateur->name, 0, 1)) }}</span>
                            <span class="profil-nom">{{ strtok($utilisateur->name, ' ') }}</span>
                        </summary>
                        <div class="menu-liste">
                            <a href="{{ route('tableau-de-bord') }}"><x-icone nom="home" :taille="18" /> Mon espace</a>
                            <a href="{{ route('abonnement.index') }}"><x-icone nom="carte" :taille="18" /> Mon abonnement</a>
                            @if (Route::has('parrainage'))
                                <a href="{{ route('parrainage') }}"><x-icone nom="cadeau" :taille="18" /> Parrainage</a>
                            @endif
                            <form method="POST" action="{{ route('deconnexion') }}">
                                @csrf
                                <button type="submit"><x-icone nom="sortie" :taille="18" /> Déconnexion</button>
                            </form>
                        </div>
                    </details>
                </div>
            </header>

            <main class="app-main">
                @yield('contenu')
            </main>

            <footer class="pied">© {{ date('Y') }} {{ config('app.name') }} · <a href="{{ route('aide') }}">Aide</a></footer>
        </div>
    </div>
@else
    <header class="nav" id="nav">
        <div class="conteneur nav-in">
            <a class="logo" href="{{ route('accueil') }}" aria-label="{{ config('app.name') }} : accueil"><x-logo /></a>

            <nav class="nav-liens" aria-label="Navigation principale">
                <details class="menu-deroulant">
                    <summary>Catalogue <span aria-hidden="true">▾</span></summary>
                    <div class="menu-liste">
                        @foreach ($navFilieres as $f)
                            <a href="{{ route('filieres.show', $f->slug) }}">{{ $f->nom }}</a>
                        @endforeach
                    </div>
                </details>
                @if (Route::has('direct.index'))<a href="{{ route('direct.index') }}">Cours en direct</a>@endif
                @if (Route::has('annales.index'))<a href="{{ route('annales.index') }}">Annales</a>@endif
                @if (Route::has('atelier.index'))<a href="{{ route('atelier.index') }}">Atelier de code</a>@endif
                @if (Route::has('classement'))<a href="{{ route('classement') }}">Classement</a>@endif
                <a href="{{ route('aide') }}">Aide</a>
            </nav>

            <div class="nav-droite">
                <a class="nav-icone" href="{{ route('recherche') }}" aria-label="Rechercher"><x-icone nom="recherche" /></a>
                <x-bascule-mode />
                <a class="btn btn-contour btn-sm" href="{{ route('connexion') }}">Connexion</a>
                <a class="btn btn-primaire btn-sm" href="{{ route('inscription') }}">S'inscrire</a>
                <button type="button" class="nav-burger" id="nav-burger" aria-label="Menu"><x-icone nom="menu" /></button>
            </div>
        </div>
    </header>

    <main>
        @yield('contenu')
    </main>

    <footer class="pied-pro">
        <div class="conteneur">
            <div class="pied-grille">
                <div>
                    <a class="logo" href="{{ route('accueil') }}" aria-label="{{ config('app.name') }} : accueil"><x-logo /></a>
                    <p style="margin-top:.8rem">La plateforme d'apprentissage des étudiants du Congo : cours, annales, quiz, coach IA et cours en direct.</p>
                </div>
                <div>
                    <h4>Apprendre</h4>
                    @foreach ($navFilieres as $f)<a href="{{ route('filieres.show', $f->slug) }}">{{ $f->nom }}</a>@endforeach
                    @if (Route::has('direct.index'))<a href="{{ route('direct.index') }}">Cours en direct</a>@endif
                </div>
                <div>
                    <h4>Communauté</h4>
                    @if (Route::has('annales.index'))<a href="{{ route('annales.index') }}">Annales</a>@endif
                    @if (Route::has('classement'))<a href="{{ route('classement') }}">Classement</a>@endif
                    <a href="{{ route('inscription') }}">Créer un compte</a>
                </div>
                <div>
                    <h4>Aide</h4>
                    <a href="{{ route('aide') }}">Centre d'aide</a>
                    @if ($support)<a href="{{ $support }}" target="_blank" rel="noopener">Contacter le support</a>@endif
                    <a href="{{ route('connexion') }}">Connexion</a>
                </div>
            </div>
            <div class="pied-bas">© {{ date('Y') }} {{ config('app.name') }} · Pointe-Noire, Congo</div>
        </div>
    </footer>
@endauth

@include('partials.aide-widget')

<script>
    document.getElementById('nav-burger')?.addEventListener('click', () => document.getElementById('nav').classList.toggle('nav-ouvert'));
    document.getElementById('side-burger')?.addEventListener('click', () => document.body.classList.toggle('menu-ouvert'));
    document.getElementById('voile')?.addEventListener('click', () => document.body.classList.remove('menu-ouvert'));
    document.addEventListener('click', (e) => {
        document.querySelectorAll('details.menu-deroulant[open]').forEach((d) => {
            if (!d.contains(e.target)) d.removeAttribute('open');
        });
    });
</script>

@stack('scripts')
</body>
</html>