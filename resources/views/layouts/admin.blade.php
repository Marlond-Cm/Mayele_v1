<!DOCTYPE html>
<html lang="fr" data-theme="mayele">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre', 'Administration') · {{ config('app.name') }} Admin</title>
    @include('partials.head-marque')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin">
    <aside class="admin-menu">
    <a class="logo" href="{{ route('admin.tableau') }}" aria-label="Administration"><x-logo :taille="30" /><small>Admin</small></a>
        <nav>
            <a href="{{ route('admin.tableau') }}" class="{{ request()->routeIs('admin.tableau') ? 'actif' : '' }}">Tableau de bord</a>
            <a href="{{ route('admin.matieres.index') }}" class="{{ request()->routeIs('admin.matieres.*', 'admin.chapitres.*') ? 'actif' : '' }}">Matières et cours</a>
            <a href="{{ route('admin.annales.index') }}" class="{{ request()->routeIs('admin.annales.*') ? 'actif' : '' }}">Annales</a>
            <a href="{{ route('admin.quiz.index') }}" class="{{ request()->routeIs('admin.quiz.*') ? 'actif' : '' }}">Quiz</a>
            <a href="{{ route('admin.coach') }}" class="{{ request()->routeIs('admin.coach') ? 'actif' : '' }}">Coach IA</a>
            <a href="{{ route('admin.universites.index') }}" class="{{ request()->routeIs('admin.universites.*') ? 'actif' : '' }}">Universités</a>
            <a href="{{ route('admin.plans.index') }}" class="{{ request()->routeIs('admin.plans.*') ? 'actif' : '' }}">Tarifs</a>
            <a href="{{ route('admin.paiements.index') }}" class="{{ request()->routeIs('admin.paiements.*') ? 'actif' : '' }}">Paiements</a>
            <a href="{{ route('admin.surveillance') }}" class="{{ request()->routeIs('admin.surveillance*') ? 'actif' : '' }}">Surveillance</a>
            <a href="{{ route('admin.aide') }}" class="{{ request()->routeIs('admin.aide') ? 'actif' : '' }}">Aide</a>
            <a href="{{ route('admin.utilisateurs.index') }}" class="{{ request()->routeIs('admin.utilisateurs.*') ? 'actif' : '' }}">Utilisateurs</a>
            <a href="{{ route('admin.direct.index') }}" class="{{ request()->routeIs('admin.direct.*') ? 'actif' : '' }}">Cours en direct</a>
            <a href="{{ route('admin.exercices.index') }}" class="{{ request()->routeIs('admin.exercices.*') ? 'actif' : '' }}">Exercices de code</a>
        </nav>
        <a href="{{ route('accueil') }}">← Voir le site</a>
        <x-bascule-mode />
        <form method="POST" action="{{ route('deconnexion') }}">
            @csrf
            <button type="submit" class="btn btn-contour btn-sm">Déconnexion</button>
        </form>
    </aside>

    <main class="admin-contenu">
        @if (session('succes'))
            <div class="alerte alerte-ok" style="margin-bottom:1rem">{{ session('succes') }}</div>
        @endif
        @if (session('erreur'))
            <div class="alerte" style="margin-bottom:1rem">{{ session('erreur') }}</div>
        @endif
        @if ($errors->any())
            <div class="alerte" style="margin-bottom:1rem">Corrige les erreurs signalées dans le formulaire.</div>
        @endif

        @yield('contenu')
    </main>

    @stack('scripts')
</body>
</html>