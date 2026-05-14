@extends('layouts.app')

@section('content')
<section class="animations-page">
    <div class="animations-page__container">
        <header class="animations-page__hero">
            <p class="animations-page__eyebrow">Animation & Video Portfolio</p>
            <h1 class="animations-page__title">Animation, Motion Graphics & Video Projects</h1>
            <p class="animations-page__lead">
                A curated selection of animation, music video, subtitle, commercial, and visual storytelling projects created for artists, brands, and digital campaigns.
            </p>
        </header>

        @if ($portfolioPosts->isNotEmpty())
            <div class="animations-page__grid">
                @foreach ($portfolioPosts as $post)
                    @php($thumbnailUrl = $post->thumbnailUrl())
                    <article class="animations-page__card">
                        <div class="animations-page__image">
                            <img src="{{ $thumbnailUrl }}" alt="{{ $post->title }} thumbnail">
                        </div>

                        <div class="animations-page__card-body">
                            <p class="animations-page__category">{{ $post->category }}</p>
                            <h2>{{ $post->title }}</h2>
                            <p class="animations-page__description">{{ str($post->short_description)->limit(140) }}</p>
                        </div>

                        <a href="{{ route('graphics.show', $post) }}" class="animations-page__button">View Project</a>
                    </article>
                @endforeach
            </div>

            {{ $portfolioPosts->onEachSide(1)->links('pagination.animations') }}
        @else
            <div class="animations-page__empty">
                <p>Portfolio posts will appear here soon.</p>
            </div>
        @endif
    </div>
</section>
@endsection
