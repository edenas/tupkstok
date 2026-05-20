@extends('layouts.app')

@section('content')
@php($projectDetails = $portfolioPost->localizedProjectDetails())
@php($url = \App\Support\LocalizedUrl::class)

<section class="animation-post-page">
    <div class="animation-post-page__container">
        <a href="{{ $url::route('graphics') }}" class="animation-post-page__back-link">{{ __('messages.common.back_to_graphics') }}</a>

        <header class="animation-post-page__hero">
            <p class="animation-post-page__category">{{ $portfolioPost->localizedCategory() }}</p>
            <h1 class="animation-post-page__title">{{ $portfolioPost->localizedTitle() }}</h1>
        </header>

        @if ($youtubeEmbedUrl = $portfolioPost->youtubeEmbedUrl())
            <section class="animation-post-page__video-card">
                <h2 class="animation-post-page__video-title">{{ __('messages.graphics.video') }}</h2>
                <div class="animation-post-page__video-frame">
                    <iframe
                        src="{{ $youtubeEmbedUrl }}"
                        title="{{ $portfolioPost->localizedTitle() }} video"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                    ></iframe>
                </div>
            </section>
        @endif

        <div class="animation-post-page__details-row {{ $portfolioPost->post_image ? 'animation-post-page__details-row--with-image' : '' }}">
            <article class="animation-post-page__description-card">
                <div class="animation-post-page__description">
                    @if ($portfolioPost->localizedContentHeading())
                        <h2>{{ $portfolioPost->localizedContentHeading() }}</h2>
                    @endif

                    @if ($portfolioPost->localizedDescription())
                        <p>{{ $portfolioPost->localizedDescription() }}</p>
                    @endif
                </div>
            </article>

            <div class="animation-post-page__side-column">
                @if ($portfolioPost->post_image)
                    <article class="animation-post-page__image-card">
                        <div class="animation-post-page__image">
                            <img src="{{ $portfolioPost->postImageUrl() }}" alt="{{ $portfolioPost->localizedTitle() }} project image">
                        </div>
                    </article>
                @endif

                @if (! empty($projectDetails))
                    <section class="animation-post-page__details-card">
                        <h2>{{ __('messages.graphics.details') }}</h2>
                        <ul>
                            @foreach ($projectDetails as $detail)
                                <li>
                                    <span class="animation-post-page__detail-check" aria-hidden="true">
                                        <svg viewBox="0 0 20 20" focusable="false">
                                            <path fill="currentColor" fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 0 1 1.4-1.4L8 12.59l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                    {{ $detail }}
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
