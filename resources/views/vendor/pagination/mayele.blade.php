@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Pagination">
        <p class="pagination-resume">
            Résultats {{ $paginator->firstItem() }} à {{ $paginator->lastItem() }} sur {{ $paginator->total() }}
        </p>

        <ul class="pagination-liste">
            @if ($paginator->onFirstPage())
                <li><span class="page page-desactive" aria-disabled="true">‹ Précédent</span></li>
            @else
                <li><a class="page" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Précédent</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="page page-desactive">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="page page-actuelle" aria-current="page">{{ $page }}</span></li>
                        @else
                            <li><a class="page" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a class="page" href="{{ $paginator->nextPageUrl() }}" rel="next">Suivant ›</a></li>
            @else
                <li><span class="page page-desactive" aria-disabled="true">Suivant ›</span></li>
            @endif
        </ul>
    </nav>
@endif