@extends('layouts.app')

@section('titre', $quiz->titre)

@section('contenu')
<div class="conteneur section" style="max-width:820px">
    <p class="fil-ariane">
        <a href="{{ route('filieres.show', $quiz->matiere->filiere->slug) }}">{{ $quiz->matiere->filiere->nom }}</a> ›
        <a href="{{ route('matieres.show', $quiz->matiere->slug) }}">{{ $quiz->matiere->nom }}</a> › Quiz
    </p>

    <h1>{{ $quiz->titre }}</h1>
    @if ($quiz->description)
        <p class="muted">{{ $quiz->description }}</p>
    @endif

    <div class="tags" style="margin-bottom:1.2rem">
        <span class="tag">{{ $nbQuestions }} questions</span>
        <span class="tag">{{ $quiz->points_par_question }} pts par bonne réponse</span>
        @if ($meilleur !== null)
            <span class="tag tag-ok">Ton meilleur score : {{ $meilleur }} pts</span>
        @endif
    </div>

    @if (! $user)
        <div class="carte verrou">
            <div class="icone">🎯</div>
            <h2>Connecte-toi pour jouer</h2>
            <p class="muted">Tes points comptent pour le classement de ton université.</p>
            <a class="btn btn-primaire" href="{{ route('connexion') }}">Se connecter</a>
            <a class="btn btn-contour" href="{{ route('inscription', ['filiere' => $quiz->matiere->filiere_id]) }}">Créer un compte</a>
        </div>
    @elseif (! $accessible)
        <div class="carte verrou">
            <div class="icone">🔒</div>
            <h2>Quiz réservé aux abonnés</h2>
            <p class="muted">Abonne-toi à {{ $quiz->matiere->filiere->nom }} pour jouer et gagner des points.</p>
            <a class="btn btn-primaire" href="{{ route('abonnement.index', ['filiere' => $quiz->matiere->filiere->slug]) }}">S'abonner</a>
        </div>
    @else
        <form method="POST" action="{{ route('quiz.soumettre', $quiz) }}">
            @csrf

            @foreach ($questions as $question)
                <fieldset class="quiz-question">
                    <legend>Question {{ $loop->iteration }} / {{ $questions->count() }}</legend>
                    <p>{{ $question->enonce }}</p>

                    @foreach ($question->choix as $choix)
                        <label class="quiz-choix">
                            <input type="radio" name="reponses[{{ $question->id }}]" value="{{ $choix->id }}" required>
                            <span>{{ $choix->libelle }}</span>
                        </label>
                    @endforeach
                </fieldset>
            @endforeach

            <button type="submit" class="btn btn-primaire btn-bloc">Valider mes réponses</button>
        </form>
    @endif
</div>
@endsection