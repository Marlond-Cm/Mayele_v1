<script>
    // Applique le thème (clair ou sombre) avant l'affichage de la page : pas de clignotement
    (function () {
        var racine = document.documentElement;

        function mode() {
            try {
                var m = localStorage.getItem('mayele-mode');
                if (m === 'clair' || m === 'sombre') return m;
            } catch (e) { /* stockage indisponible */ }
            return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'sombre' : 'clair';
        }

        racine.setAttribute('data-mode', mode());

        // Bouton lune / soleil (fonctionne partout, même pour les boutons ajoutés plus tard)
        document.addEventListener('click', function (e) {
            var bouton = e.target.closest ? e.target.closest('[data-bascule-mode]') : null;
            if (!bouton) return;
            var suivant = racine.getAttribute('data-mode') === 'sombre' ? 'clair' : 'sombre';
            racine.setAttribute('data-mode', suivant);
            try { localStorage.setItem('mayele-mode', suivant); } catch (err) { /* sans importance */ }
        });

        // Sans choix enregistré, on suit le réglage de l'appareil
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
                try { if (localStorage.getItem('mayele-mode')) return; } catch (e) { /* ignoré */ }
                racine.setAttribute('data-mode', mode());
            });
        }
    })();
</script>

<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">
<meta name="theme-color" content="#0d2b24">

<meta name="description" content="@yield('description', 'Cours, annales, quiz et cours en direct pour les étudiants du Congo.')">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:title" content="@yield('titre', 'Accueil') · {{ config('app.name') }}">
<meta property="og:description" content="@yield('description', 'Cours, annales, quiz et cours en direct pour les étudiants du Congo.')">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ asset('og-image.png') }}">
<meta name="twitter:card" content="summary_large_image">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Bricolage+Grotesque:wght@600;700;800&display=swap" rel="stylesheet">