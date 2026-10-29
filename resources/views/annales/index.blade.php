@extends('layouts.app')

@section('titre', $matiere ? 'Annales · '.$matiere->nom : 'Annales')

@section('contenu')
<section class="hero hero-compact">
    <div class="conteneur">
        <span class="badge">Annales corrigées</span>
        <h1>{{ $matiere ? $matiere->nom : 'Annales d\'examens' }}</h1>
        <p class="lead">Les sujets et corrigés des universités, pour t'entraîner dans les conditions réelles de l'examen.</p>
        <a class="btn btn-primaire" href="{{ route('annales.deposer', $matiere ? ['matiere' => $matiere->slug] : []) }}">Déposer une annale</a>
    </div>
</section>

<div class="conteneur section">
    <form class="filtres" method="GET">
        @if ($matiere)
            <input type="hidden" name="matiere" value="{{ $matiere->slug }}">
        @else
            <select name="filiere">
                <option value="">Toutes les filières</option>
                @foreach ($filieres as $f)
                    <option value="{{ $f->slug }}" @selected($filiere?->id === $f->id)>{{ $f->nom }}</option>
                @endforeach
            </select>
            <select name="niveau">
                <option value="">Tous niveaux</option>
                @foreach (['L1', 'L2', 'L3'] as $n)
                    <option value="{{ $n }}" @selected($niveau === $n)>{{ $n }}</option>
                @endforeach
            </select>
        @endif

        <select name="universite">
            <option value="">Toutes les universités</option>
            @foreach ($universites as $u)
                <option value="{{ $u->id }}" @selected((int) request('universite') === $u->id)>{{ $u->sigle ?: $u->nom }}</option>
            @endforeach
        </select>
        <select name="annee">
            <option value="">Toutes les années</option>
            @foreach ($annees as $a)
                <option value="{{ $a }}" @selected((int) request('annee') === $a)>{{ $a }}</option>
            @endforeach
        </select>
        <button class="btn btn-contour btn-sm" type="submit">Filtrer</button>
        @if ($matiere)
            <a href="{{ route('annales.index') }}">Toutes les annales</a>
        @endif
    </form>

    <div class="liste-annales">
        @forelse ($annales as $annale)
            @php($acces = $user && ($user->estAdmin() || in_array($annale->matiere->filiere_id, $abonnesFilieres, true)))
            <article class="annale" data-theme="{{ $annale->matiere->filiere->theme }}">
                <div class="annale-infos">
                    <h3>{{ $annale->matiere->nom }}</h3>
                    <div class="tags">
                        <span class="tag">{{ $annale->matiere->niveau }}</span>
                        <span class="tag">{{ $annale->universite->sigle ?: $annale->universite->nom }}</span>
                        <span class="tag">{{ $annale->annee }}</span>
                        <span class="tag">{{ $annale->session === 'rattrapage' ? 'Rattrapage' : 'Session normale' }}</span>
                        <span class="tag">{{ ['examen' => 'Examen', 'partiel' => 'Partiel', 'td' => 'TD'][$annale->type] ?? $annale->type }}</span>
                        @if ($annale->fichier_corrige)<span class="tag tag-ok">Corrigé inclus</span>@endif
                    </div>
                </div>

                <div class="annale-actions">
                    @if ($acces)
                        <a class="btn btn-primaire btn-sm" target="_blank" href="{{ route('annales.fichier', [$annale, 'sujet']) }}">Sujet</a>
                        @if ($annale->fichier_corrige)
                            <a class="btn btn-contour btn-sm" target="_blank" href="{{ route('annales.fichier', [$annale, 'corrige']) }}">Corrigé</a>
                        @endif
                        <a class="btn btn-contour btn-sm" href="{{ route('annales.fichier', [$annale, 'sujet', 'telecharger' => 1]) }}" title="Télécharger le sujet">⬇</a>
                    @elseif ($user)
                        <a class="btn btn-primaire btn-sm" href="{{ route('abonnement.index', ['filiere' => $annale->matiere->filiere->slug]) }}">🔒 S'abonner pour accéder</a>
                    @else
                        <a class="btn btn-primaire btn-sm" href="{{ route('connexion') }}">🔒 Se connecter</a>
                    @endif
                </div>
            </article>
        @empty
            <p class="muted">Aucune annale pour ces critères. Sois le premier à en déposer !</p>
        @endforelse
    </div>

    <div style="margin-top:1.5rem">{{ $annales->links() }}</div>
</div>
@endsection