@props(['taille' => 34, 'texte' => true])

<span class="logo-bloc">
    <svg class="logo-svg" width="{{ $taille }}" height="{{ $taille }}" viewBox="0 0 512 512" aria-hidden="true">
        <rect width="512" height="512" rx="116" style="fill: var(--logo-tuile, #0d2b24)"/>
        <path d="M90 385 L90 145 L168 145 L256 237 L344 145 L422 145 L422 385 L366 385 L366 203 L256 318 L146 203 L146 385 Z" fill="#ffffff"/>
        <path d="M256 127 L300 171 L256 215 L212 171 Z" fill="#f5b301"/>
    </svg>
    @if ($texte)
        <span class="logo-texte">{{ config('app.name') }}</span>
    @endif
</span>