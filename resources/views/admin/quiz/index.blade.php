@extends('layouts.admin')

@section('titre', 'Quiz')

@section('contenu')
<div class="admin-entete">
    <h1>Quiz</h1>
    <a class="btn btn-primaire" href="{{ route('admin.quiz.create') }}">+ Nouveau quiz</a>
</div>

<form class="filtres" method="GET">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher un quiz">
    <button class="btn btn-contour btn-sm" type="submit">Filtrer</button>
</form>

<div class="carte" style="overflow-x:auto">
    <table class="tableau">
        <thead><tr><th>Quiz</th><th>Matière</th><th>Questions</th><th>Tentatives</th><th>Accès</th><th>Statut</th><th></th></tr></thead>
        <tbody>
            @forelse ($quizzes as $quiz)
                <tr>
                    <td>{{ $quiz->titre }}</td>
                    <td>{{ $quiz->matiere->nom }}<br><small class="muted">{{ $quiz->matiere->filiere->nom }} · {{ $quiz->matiere->niveau }}</small></td>
                    <td>{{ $quiz->questions_count }}</td>
                    <td>{{ $quiz->tentatives_count }}</td>
                    <td><span class="badge {{ $quiz->est_gratuit ? 'badge-ok' : '' }}">{{ $quiz->est_gratuit ? 'Gratuit' : 'Abonnés' }}</span></td>
                    <td><span class="badge {{ $quiz->publie ? 'badge-ok' : 'badge-attente' }}">{{ $quiz->publie ? 'Publié' : 'Brouillon' }}</span></td>
                    <td>
                        <div class="actions-ligne">
                            <a class="btn btn-contour btn-sm" href="{{ route('admin.quiz.edit', $quiz) }}">Modifier</a>
                            <form method="POST" action="{{ route('admin.quiz.destroy', $quiz) }}" onsubmit="return confirm('Supprimer ce quiz et toutes ses tentatives ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Aucun quiz.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:1rem">{{ $quizzes->links() }}</div>
@endsection