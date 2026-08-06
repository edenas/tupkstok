@extends('layouts.app')

@section('content')
<section class="animations-page">
    <div class="animations-page__container">
        <header class="animations-page__hero">
            <h1 class="animations-page__title">{{ __('messages.blog.title') }}</h1>
        </header>

        @if ($blogPosts->isNotEmpty())
            <div class="animations-page__grid">
                @foreach ($blogPosts as $post)
                    @php($thumbnailUrl = $post->thumbnailUrl())
                    <a href="{{ route('blog.show', $post) }}" class="animations-page__card" aria-label="{{ __('messages.common.view_project') }}: {{ $post->localizedTitle() }}">
                        <div class="animations-page__image">
                            <img src="{{ $thumbnailUrl }}" alt="{{ $post->localizedTitle() }} miniatiūra" loading="lazy" decoding="async">
                        </div>

                        <div class="animations-page__card-body">
                            <p class="animations-page__category">{{ $post->localizedCategory() }}</p>
                            <h2>{{ $post->localizedTitle() }}</h2>
                            <p class="animations-page__description">{{ str(strip_tags($post->localizedDescription() ?? ''))->limit(140) }}</p>
                        </div>

                        <span class="animations-page__cta">
                            {{ __('messages.common.view_project') }}
                            <span class="animations-page__cta-arrow" aria-hidden="true">&rarr;</span>
                        </span>
                    </a>
                @endforeach
            </div>

            {{ $blogPosts->onEachSide(1)->links('pagination.animations') }}
        @else
            <div class="animations-page__empty">
                <p>{{ __('messages.blog.empty') }}</p>
            </div>
        @endif
    </div>
</section>
@endsection
