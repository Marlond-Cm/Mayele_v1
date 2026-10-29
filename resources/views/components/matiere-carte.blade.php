@props(['matiere', 'theme' => null, 'fait' => null, 'total' => null])

@php
    $theme = $theme ?? $matiere->filiere?->theme ?? 'mayele';
    $total = $total ?? $matiere->chapitres_total ?? $matiere->chapitres_count ?? null;
    $pct = ($total ?? 0) > 0 && $fait !== null ? (int) round($fait / $total * 100) : null;
@endphp
<a class="cours-carte" data-theme="{{ $theme }}" data-nom="{{ \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($matiere->nom)) }}"
   href="{{ route('matieres.show', $matiere->slug) }}">
    <div class="cours-couverture">
        <span class="cours-icone" aria-hidden="true"><x-filiere-icone :theme="$theme" :taille="30" /></span>
        <span class="cours-niveau">{{ $matiere->niveau }} · Semestre {{ $matiere->semestre }}</span>
    </div>
    <div class="cours-corps">
        <h3>{{ $matiere->nom }}</h3>
        @if ($matiere->parcours)<small>{{ $matiere->parcours }}</small>@endif
        <div class="cours-pied">
            @if ($pct !== null)
                <div class="barre"><span @style(['width' => $pct . '%'])></span></div>
                <small>{{ $fait }} / {{ $total }} chapitres · {{ $pct }} %</small>
            @elseif ($total !== null)
                <small>{{ $total > 0 ? $total.' chapitre'.($total > 1 ? 's' : '') : 'Chapitres bientôt disponibles' }}</small>
            @endif
        </div>
    </div>
</a>