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
            $tabletScreenshots = $collectScreenshots(['mobile-apps/drink-water/tablet']);
            $phoneScreenshots = array_slice($phoneScreenshots, 0, 8);
            $tabletScreenshots = array_slice($tabletScreenshots, 0, 2);
            $googlePlayHeaderUrl = asset('storage/mobile-apps/drink-water/google_play_header.jpg');
            $technologyIcons = ['code', 'package', 'brackets', 'monitor-smartphone', 'tablet-smartphone'];
            $heroFeatures = app()->getLocale() === 'lt'
                ? [
                    ['icon' => 'droplet', 'label' => 'Sekti kiekį'],
                    ['icon' => 'bell', 'label' => 'Išmanūs priminimai'],
                    ['icon' => 'chart-column', 'label' => 'Statistika'],
                    ['icon' => 'target', 'label' => 'Tikslai'],
                ]
                : [
                    ['icon' => 'droplet', 'label' => 'Track Intake'],
                    ['icon' => 'bell', 'label' => 'Smart Reminders'],
                    ['icon' => 'chart-column', 'label' => 'View Statistics'],
                    ['icon' => 'target', 'label' => 'Reach Your Goals'],
                ];
            $compactCards = [
                [
                    'title' => __('messages.mobile.about'),
                    'icon' => 'info',
                    'text' => \Illuminate\Support\Str::limit(__('messages.mobile.about_paragraphs')[0], 185),
                ],
                [
                    'title' => __('messages.mobile.technologies'),
                    'icon' => 'layers',
                    'technologies' => __('messages.mobile.tech_items'),
                ],
                [
                    'title' => __('messages.mobile.reminders'),
                    'icon' => 'bell',
                    'text' => \Illuminate\Support\Str::limit(__('messages.mobile.reminder_paragraphs')[0], 170),
                ],
                [
                    'title' => app()->getLocale() === 'lt' ? __('messages.mobile.design') : 'Design & UX',
                    'icon' => 'alarm-clock',
                    'text' => \Illuminate\Support\Str::limit(__('messages.mobile.design_paragraphs')[0], 170),
                ],
            ];
        @endphp

        <header class="mobile-app-page__hero">
            <div class="mobile-app-page__hero-content">
                <p class="mobile-app-page__eyebrow">{{ __('messages.mobile.eyebrow') }}</p>
                <h1 class="mobile-app-page__title">Drink Water</h1>
                <p class="mobile-app-page__lead">
                    {{ __('messages.mobile.lead') }}
                </p>
                <div class="mobile-app-page__hero-actions">
                    <button type="button" class="mobile-app-page__download-button" disabled>Google Play</button>
                    <button type="button" class="mobile-app-page__download-button mobile-app-page__download-button--secondary" disabled>APK version</button>
                </div>
                <ul class="mobile-app-page__hero-features" aria-label="{{ __('messages.mobile.features') }}">
                    @foreach ($heroFeatures as $feature)
                        <li>
                            <span class="mobile-app-page__hero-feature-icon" aria-hidden="true">
                                <i data-lucide="{{ $feature['icon'] }}"></i>
                            </span>
                            <span>{{ $feature['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <figure class="mobile-app-page__hero-preview" aria-label="Drink Water Google Play preview">
                <img src="{{ $googlePlayHeaderUrl }}" alt="Drink Water Google Play header" loading="eager">
            </figure>
        </header>

        <section class="mobile-app-page__summary-grid" aria-label="{{ __('messages.mobile.about') }}">
            @foreach ($compactCards as $card)
                <article class="mobile-app-page__summary-card">
                    <div class="mobile-app-page__summary-heading">
                        <span class="mobile-app-page__summary-icon" aria-hidden="true">
                            <i data-lucide="{{ $card['icon'] }}"></i>
                        </span>
                        <h2>{{ $card['title'] }}</h2>
                    </div>

                    @if (isset($card['technologies']))
                        <ul class="mobile-app-page__summary-tech-list">
                            @foreach ($card['technologies'] as $index => $technology)
                                <li>
                                    <span class="mobile-app-page__summary-tech-icon" aria-hidden="true">
                                        <i data-lucide="{{ $technologyIcons[$index] ?? 'code' }}"></i>
                                    </span>
                                    <span>{{ $technology }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p>{{ $card['text'] }}</p>
                    @endif
                </article>
            @endforeach
        </section>

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

        <section class="mobile-app-page__screenshots" id="mobile-app-screenshots">
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
                                    data-mobile-screenshot-lightbox-trigger
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
                                    data-mobile-screenshot-lightbox-trigger
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

        <div class="mobile-app-page__lightbox" data-mobile-screenshot-lightbox aria-hidden="true" hidden>
            <button type="button" class="mobile-app-page__lightbox-close" data-mobile-screenshot-lightbox-close aria-label="Close image preview">X</button>
            <img class="mobile-app-page__lightbox-image" data-mobile-screenshot-lightbox-image src="" alt="">
        </div>
    </div>
</section>
@endsection
