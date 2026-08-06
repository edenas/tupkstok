@extends('layouts.app')

@section('content')
@php($articleInformation = $blogPost->articleInformation())
@php($sourceText = trim((string) $articleInformation['source']))
@php($sourceHref = preg_match('/^(https?:\/\/)?([a-z0-9-]+\.)+[a-z]{2,}(\/.*)?$/i', $sourceText) ? (preg_match('/^https?:\/\//i', $sourceText) ? $sourceText : 'https://'.$sourceText) : null)
@php($articleContent = app(\App\Services\BlogContentSanitizer::class)->sanitize($blogPost->localizedDescription()))

<section class="animation-post-page">
    <div class="animation-post-page__container">
        <a href="{{ route('blog') }}" class="animation-post-page__back-link">{{ __('messages.common.back_to_blog') }}</a>

        <header class="animation-post-page__hero">
            <p class="animation-post-page__category">{{ $blogPost->localizedCategory() }}</p>
            <h1 class="animation-post-page__title">{{ $blogPost->localizedTitle() }}</h1>
        </header>

        @if ($youtubeEmbedUrl = $blogPost->youtubeEmbedUrl())
            <section class="animation-post-page__video-card">
                <h2 class="animation-post-page__video-title">{{ __('messages.blog.video') }}</h2>
                <div class="animation-post-page__video-frame">
                    <iframe
                        src="{{ $youtubeEmbedUrl }}"
                        title="{{ $blogPost->localizedTitle() }} video"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                    ></iframe>
                </div>
            </section>
        @endif

        <div class="animation-post-page__details-row">
            <article class="animation-post-page__description-card">
                <div class="animation-post-page__description">
                    @if ($articleContent)
                        {!! $articleContent !!}
                    @endif
                </div>
            </article>

            <div class="animation-post-page__side-column">
                <section class="animation-post-page__article-info-card">
                    <h2 class="animation-post-page__article-info-title">Straipsnio informacija</h2>

                    <div class="animation-post-page__article-info-grid">
                        <dl class="animation-post-page__article-info">
                            <div>
                                <dt>Autorius</dt>
                                <dd>{{ $articleInformation['author'] }}</dd>
                            </div>
                            <div>
                                <dt>Šaltinis</dt>
                                <dd>
                                    @if ($sourceHref)
                                        <a href="{{ $sourceHref }}" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: none;">
                                            {{ $articleInformation['source'] }}
                                        </a>
                                    @else
                                        {{ $articleInformation['source'] }}
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    @if ($articleInformation['show_disclaimer'] && $articleInformation['disclaimer_text'] !== '')
                        <p class="animation-post-page__disclaimer">
                            <strong>Svarbu:</strong>
                            <span>{{ $articleInformation['disclaimer_text'] }}</span>
                        </p>
                    @endif
                </section>
            </div>
        </div>
    </div>
</section>
@endsection
