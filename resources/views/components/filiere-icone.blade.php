@props(['theme' => 'mayele', 'taille' => 28])

<svg class="icone" width="{{ $taille }}" height="{{ $taille }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($theme)
        @case('economie')
            <line x1="3" y1="21" x2="21" y2="21"/><rect x="5" y="12" width="3.2" height="7" rx=".6"/><rect x="10.4" y="7" width="3.2" height="12" rx=".6"/><rect x="15.8" y="3" width="3.2" height="16" rx=".6"/>
            @break
        @case('industrie')
            <circle cx="12" cy="12" r="9" stroke-width="3" stroke-dasharray="3.5 3.5"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2.2"/>
            @break
        @case('informatique')
            <polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/><line x1="14" y1="4" x2="10" y2="20"/>
            @break
        @default
            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
    @endswitch
</svg>