@extends('layouts.app')

@section('titre', 'Résultat · '.$quiz->titre)

@section('contenu')
<div class="conteneur section" style="max-width:820px">
    <div class="carte quiz-score">
        <p class="muted" style="margin:0">{{ $quiz->titre }}</p>
        <strong>{{ $tentative->bonnes_reponses }} / {{ $tentative->total_questions }}</strong>
        <p>+{{ $tentative->score }} points</p>
        <p class="muted"><small>Ton meilleur score sur ce quiz : {{ $meilleur }} pts (seul le meilleur compte dans le classement).</small></p>
        <div class="actions-chapitre" style="justify-content:center">
            <a class="btn btn-primaire" href="{{ route('quiz.show', $quiz) }}">Rejouer</a>
            <a class="btn btn-contour" href="{{ route('classement') }}">Voir le classement</a>
            <a class="btn btn-contour" href="{{ route('matieres.show', $quiz->matiere->slug) }}">Retour à la matière</a>
        </div>
    </div>

    <h2 class="semestre-titre">Correction</h2>

    @foreach ($questions as $question)
        @php($choisiId = (int) ($tentative->reponses[$question->id] ?? 0))
        <div class="quiz-question">
            <p><strong>{{ $loop->iteration }}.</strong> {{ $question->enonce }}</p>

            @foreach ($question->choix as $choix)
                <div class="quiz-choix {{ $choix->est_correct ? 'correct' : ($choix->id === $choisiId ? 'faux' : '') }}">
                    <span>{{ $choix->libelle }}</span>
                    @if ($choix->est_correct)
                        <strong>✓</strong>
                    @elseif ($choix->id === $choisiId)
                        <strong>✗ ta réponse</strong>
                    @endif
                </div>
            @endforeach

            @if ($question->explication)
                <div class="explication">{{ $question->explication }}</div>
            @endif
        </div>
    @endforeach
</div>
@endsection