@if ($paginator->hasPages())
    <nav class="admin-pagination" role="navigation" aria-label="Admin pagination">
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="admin-pagination__ellipsis" aria-hidden="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="admin-pagination__link admin-pagination__link--active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="admin-pagination__link" href="{{ $url }}" aria-label="Open page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
    </nav>
@endif
