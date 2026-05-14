@if ($paginator->hasPages())
    <nav class="animations-page__pagination" role="navigation" aria-label="Graphics pagination">
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="animations-page__pagination-ellipsis" aria-hidden="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="animations-page__pagination-link animations-page__pagination-link--active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="animations-page__pagination-link" href="{{ $url }}" aria-label="Open page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
    </nav>
@endif
