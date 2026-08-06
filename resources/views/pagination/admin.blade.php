@if ($paginator->hasPages())
    <nav class="admin-pagination" role="navigation" aria-label="Administravimo puslapiavimas">
        @if ($paginator->onFirstPage())
            <span class="admin-pagination__link admin-pagination__link--disabled" aria-disabled="true" aria-label="Ankstesnis puslapis">&lsaquo;</span>
        @else
            <a class="admin-pagination__link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Ankstesnis puslapis">&lsaquo;</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="admin-pagination__ellipsis" aria-hidden="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="admin-pagination__link admin-pagination__link--active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="admin-pagination__link" href="{{ $url }}" aria-label="Atidaryti puslapį {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="admin-pagination__link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Kitas puslapis">&rsaquo;</a>
        @else
            <span class="admin-pagination__link admin-pagination__link--disabled" aria-disabled="true" aria-label="Kitas puslapis">&rsaquo;</span>
        @endif
    </nav>
@endif
