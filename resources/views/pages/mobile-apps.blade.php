@extends('layouts.app')

@section('content')
<section class="mobile-app-page">
    <div class="mobile-app-page__container">
        @php
            $screenshotExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $collectScreenshots = function (array $directories) use ($screenshotExtensions) {
                $screenshots = [];

                foreach ($directories as $directory) {
                    if (! \Illuminate\Support\Facades\Storage::disk('public')->exists($directory)) {
                        continue;
                    }

                    foreach (\Illuminate\Support\Facades\Storage::disk('public')->files($directory) as $path) {
                        if (! in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), $screenshotExtensions, true)) {
                            continue;
                        }

                        $screenshots[$path] = [
                            'name' => pathinfo($path, PATHINFO_FILENAME),
                            'url' => asset('storage/'.$path),
                        ];
                    }
                }

                ksort($screenshots, SORT_NATURAL | SORT_FLAG_CASE);

                return array_values($screenshots);
            };

            $phoneScreenshots = $collectScreenshots(['mobile-apps/drink-water/phone']);
            $tabletScreenshots = $collectScreenshots([
                'mobile-apps/drink-water/tablet',
                'mobile-apps/drink-water/phone/tablet',
            ]);
            $phoneScreenshots = array_slice($phoneScreenshots, 0, 4);
            $tabletScreenshots = array_slice($tabletScreenshots, 0, 2);
            $technologyIcons = ['code', 'package', 'brackets', 'monitor-smartphone', 'tablet-smartphone'];
            $featureIcons = ['activity', 'sliders-horizontal', 'circle-gauge', 'chart-column', 'bell', 'clock', 'undo-2', 'tablet-smartphone', 'sparkles'];
        @endphp

        <header class="mobile-app-page__hero">
            <p class="mobile-app-page__eyebrow">{{ __('messages.mobile.eyebrow') }}</p>
            <h1 class="mobile-app-page__title">Drink Water</h1>
            <p class="mobile-app-page__lead">
                {{ __('messages.mobile.lead') }}
            </p>
        </header>

        <div class="mobile-app-page__layout">
            <article class="mobile-app-page__section mobile-app-page__section--large">
                <h2>{{ __('messages.mobile.about') }}</h2>
                @foreach (__('messages.mobile.about_paragraphs') as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </article>

            <aside class="mobile-app-page__section">
                <h2>{{ __('messages.mobile.technologies') }}</h2>
                <ul class="mobile-app-page__tag-list">
                    @foreach (__('messages.mobile.tech_items') as $index => $technology)
                        <li>
                            <span class="mobile-app-page__item-icon" aria-hidden="true">
                                <i data-lucide="{{ $technologyIcons[$index] ?? 'code' }}"></i>
                            </span>
                            <span>{{ $technology }}</span>
                        </li>
                    @endforeach
                </ul>
            </aside>
        </div>

        <section class="mobile-app-page__section">
            <h2>{{ __('messages.mobile.features') }}</h2>
            <ul class="mobile-app-page__feature-grid">
                @foreach (__('messages.mobile.feature_items') as $index => $feature)
                    <li>
                        <span class="mobile-app-page__item-icon" aria-hidden="true">
                            <i data-lucide="{{ $featureIcons[$index] ?? 'sparkles' }}"></i>
                        </span>
                        <span>{{ $feature }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <div class="mobile-app-page__layout">
            <section class="mobile-app-page__section">
                <h2>{{ __('messages.mobile.reminders') }}</h2>
                @foreach (__('messages.mobile.reminder_paragraphs') as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </section>

            <section class="mobile-app-page__section">
                <h2>{{ __('messages.mobile.design') }}</h2>
                @foreach (__('messages.mobile.design_paragraphs') as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </section>
        </div>

        <section class="mobile-app-page__download">
            <div>
                <h2>{{ __('messages.mobile.download') }}</h2>
                <p>{{ __('messages.mobile.download_note') }}</p>
            </div>
            <div class="mobile-app-page__download-actions">
                <button type="button" class="mobile-app-page__download-button" disabled>Google Play</button>
                <button type="button" class="mobile-app-page__download-button mobile-app-page__download-button--secondary" disabled>APK version</button>
            </div>
        </section>

        <section class="mobile-app-page__screenshots">
            <div class="mobile-app-page__screenshots-header">
                <h2>{{ __('messages.mobile.screenshots') }}</h2>
                <p>{{ __('messages.mobile.screenshots_note') }}</p>
            </div>

            <div class="mobile-app-page__screenshot-showcase">
                <div class="mobile-app-page__gallery-block mobile-app-page__gallery-block--phone">
                    <div class="mobile-app-page__gallery-heading">
                        <span>{{ __('messages.mobile.phone_screenshots') }}</span>
                    </div>

                    @if ($phoneScreenshots)
                        <div class="mobile-app-page__phone-gallery" aria-label="{{ __('messages.mobile.phone_screenshots') }}">
                            @foreach ($phoneScreenshots as $screenshot)
                                <a
                                    href="{{ $screenshot['url'] }}"
                                    class="mobile-app-page__phone-frame"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="{{ __('messages.mobile.phone_screenshots') }}: {{ $screenshot['name'] }}"
                                >
                                    <img src="{{ $screenshot['url'] }}" alt="{{ $screenshot['name'] }}" loading="lazy">
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="mobile-app-page__gallery-empty">
                            <span>{{ __('messages.mobile.no_phone_screenshots') }}</span>
                        </div>
                    @endif
                </div>

                <div class="mobile-app-page__gallery-block mobile-app-page__gallery-block--tablet">
                    <div class="mobile-app-page__gallery-heading">
                        <span>{{ __('messages.mobile.tablet_screenshots') }}</span>
                    </div>

                    @if ($tabletScreenshots)
                        <div class="mobile-app-page__tablet-gallery" aria-label="{{ __('messages.mobile.tablet_screenshots') }}">
                            @foreach ($tabletScreenshots as $screenshot)
                                <a
                                    href="{{ $screenshot['url'] }}"
                                    class="mobile-app-page__tablet-frame"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="{{ __('messages.mobile.tablet_screenshots') }}: {{ $screenshot['name'] }}"
                                >
                                    <img src="{{ $screenshot['url'] }}" alt="{{ $screenshot['name'] }}" loading="lazy">
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="mobile-app-page__gallery-empty mobile-app-page__gallery-empty--wide">
                            <span>{{ __('messages.mobile.no_tablet_screenshots') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</section>
@endsection
