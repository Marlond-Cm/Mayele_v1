@extends('layouts.app')

@section('titre', $filiere->nom)

@section('contenu')
<section class="hero hero-compact">
    <div class="conteneur">
        <span class="badge">Catalogue</span>
        <h1>{{ $filiere->nom }}</h1>
        <p class="lead">{{ $filiere->description }}</p>
        <div class="chips-info">
            <span class="chip-info">{{ $nbMatieres }} matière{{ $nbMatieres > 1 ? 's' : '' }}</span>
            <span class="chip-info">{{ $totalChapitres }} chapitre{{ $totalChapitres > 1 ? 's' : '' }}</span>
            @if ($plans->isNotEmpty())
                <span class="chip-info">À partir de {{ number_format($plans->min('prix'), 0, ',', ' ') }} FCFA</span>
            @endif
        </div>
        @guest
            <a class="btn btn-primaire" href="{{ route('inscription', ['filiere' => $filiere->id]) }}">Créer mon compte</a>
        @endguest
    </div>
</section>

<div class="conteneur section">
    <div class="catalogue-outils">
        <div class="onglets" style="margin:0" id="onglets-niveau">
            <button type="button" class="onglet actif" data-niveau="">Tous les niveaux</button>
            @foreach ($matieres->keys() as $niveau)
                <button type="button" class="onglet" data-niveau="{{ $niveau }}">{{ $niveau }}</button>
            @endforeach
        </div>
        <input type="search" id="filtre-nom" placeholder="Filtrer les matières…" aria-label="Filtrer les matières">
    </div>

    @forelse ($matieres as $niveau => $parNiveau)
        <section class="catalogue-niveau" data-niveau="{{ $niveau }}">
            <h2>Niveau {{ $niveau }}</h2>

            @foreach ($parNiveau->groupBy('semestre') as $semestre => $liste)
                <h3 class="semestre-titre">Semestre {{ $semestre }}</h3>
                <div class="grille-cours">
                    @foreach ($liste as $matiere)
                        <x-matiere-carte :matiere="$matiere" :theme="$filiere->theme" :total="$matiere->chapitres_total"
                                         :fait="auth()->check() ? (int) $faits->get($matiere->id, 0) : null" />
                    @endforeach
                </div>
            @endforeach
        </section>
    @empty
        <div class="carte vide-etat"><p class="muted" style="margin:0">Le catalogue de cette filière arrive bientôt.</p></div>
    @endforelse

    <p id="filtre-aucun" class="muted" hidden>Aucune matière ne correspond à ton filtre.</p>
</div>
@endsection

@push('scripts')
<script>
    const sections = document.querySelectorAll('.catalogue-niveau');
    const cartes = document.querySelectorAll('.cours-carte');
    const champ = document.getElementById('filtre-nom');
    let niveauChoisi = '';

    const normaliser = (t) => t.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

    function filtrer() {
        const mot = normaliser(champ.value.trim());
        let visibles = 0;

        cartes.forEach((carte) => {
            const ok = mot === '' || carte.dataset.nom.includes(mot);
            carte.hidden = !ok;
        });

        sections.forEach((section) => {
            const dansNiveau = niveauChoisi === '' || section.dataset.niveau === niveauChoisi;
            const aDesCartes = section.querySelector('.cours-carte:not([hidden])') !== null;
            section.hidden = !(dansNiveau && aDesCartes);
            if (!section.hidden) visibles++;
        });

        document.getElementById('filtre-aucun').hidden = visibles > 0 || sections.length === 0;
    }

    document.querySelectorAll('#onglets-niveau .onglet').forEach((bouton) => {
        bouton.addEventListener('click', () => {
            document.querySelectorAll('#onglets-niveau .onglet').forEach((b) => b.classList.remove('actif'));
            bouton.classList.add('actif');
            niveauChoisi = bouton.dataset.niveau;
            filtrer();
        });
    });

    champ.addEventListener('input', filtrer);
</script>
@endpush