@extends('layouts.app')

@section('titre', 'Coach IA')

@section('contenu')
<section class="hero hero-compact">
    <div class="conteneur">
        <span class="badge">Coach IA</span>
        <h1>Ton coach, disponible 24h/24</h1>
        <p class="lead">Il t'explique tes cours, corrige tes exercices et t'interroge avant les examens.</p>
        <p class="muted">Aujourd'hui : <strong>{{ $restant }}</strong> / {{ $limite }} messages restants.</p>
    </div>
</section>

<div class="conteneur section">
    <div class="grille" style="margin-bottom:2.5rem">
        <div class="carte"><h3>Expliquer</h3><p class="muted">Un point du cours t'échappe ? Il te l'explique pas à pas, avec des exemples.</p></div>
        <div class="carte"><h3>Corriger mon exercice</h3><p class="muted">Envoie ton énoncé et ta réponse : il repère tes erreurs et t'explique la méthode.</p></div>
        <div class="carte"><h3>M'interroger</h3><p class="muted">Il te pose des questions une par une, comme un examinateur, et corrige tes réponses.</p></div>
    </div>

    <h2>Mes conversations</h2>

    <div class="liste-annales">
        @forelse ($conversations as $conversation)
            <article class="annale">
                <div class="annale-infos">
                    <h3>{{ $conversation->titre }}</h3>
                    <div class="tags">
                        <span class="tag">{{ $conversation->matiere->nom }}</span>
                        <span class="tag">{{ \App\Models\CoachConversation::MODES[$conversation->mode] ?? $conversation->mode }}</span>
                        <span class="tag">{{ $conversation->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="annale-actions">
                    <a class="btn btn-primaire btn-sm" href="{{ route('coach.show', $conversation) }}">Ouvrir</a>
                    <form method="POST" action="{{ route('coach.destroy', $conversation) }}" onsubmit="return confirm('Supprimer cette conversation ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-contour btn-sm" type="submit">Supprimer</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="muted">Aucune conversation pour le moment. Ouvre un chapitre et clique sur « Expliquer », « Corriger mon exercice » ou « M'interroger ».</p>
        @endforelse
    </div>
</div>
@endsection