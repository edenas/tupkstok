@extends('layouts.app')

@section('content')
@php
    $url = \App\Support\LocalizedUrl::class;

    $aboutTechItems = [
        ['label' => 'Laravel', 'icon' => 'blocks'],
        ['label' => 'PHP', 'icon' => 'braces'],
        ['label' => 'JavaScript', 'icon' => 'code'],
        ['label' => 'TypeScript', 'icon' => 'file-type'],
        ['label' => 'WooCommerce', 'icon' => 'package'],
        ['label' => 'CSS', 'icon' => 'braces'],
        ['label' => 'HTML5', 'icon' => 'brackets'],
        ['label' => 'Photoshop', 'icon' => 'palette'],
        ['label' => 'React Native', 'icon' => 'smartphone'],
        ['label' => 'Django', 'icon' => 'file-code'],
        ['label' => 'Python', 'icon' => 'code'],
        ['label' => 'Database', 'icon' => 'database'],
        ['label' => 'WordPress', 'icon' => 'globe'],
        ['label' => 'UI/UX', 'icon' => 'pen-tool'],
        ['label' => 'Motion Graphics', 'icon' => 'clapperboard'],
        ['label' => 'Animation', 'icon' => 'play'],
        ['label' => 'Responsive Design', 'icon' => 'monitor-smartphone'],
        ['label' => 'SEO', 'icon' => 'search'],
        ['label' => 'AI Integration', 'icon' => 'brain-circuit'],
        ['label' => 'Android Development', 'icon' => 'bot'],
    ];

    $aboutServiceGroups = [
        [
            'key' => 'web',
            'image' => 'images/apie_mus_web_sprendimai.jpg',
            'image_position' => 'left',
            'items' => [
                ['key' => 'website_development', 'icon' => 'globe'],
                ['key' => 'web_application_development', 'icon' => 'app-window'],
                ['key' => 'android_mobile_application_development', 'icon' => 'smartphone'],
                ['key' => 'ui_ux_design', 'icon' => 'pen-tool'],
                ['key' => 'front_end_development', 'icon' => 'code'],
                ['key' => 'responsive_design', 'icon' => 'monitor-smartphone'],
            ],
        ],
        [
            'key' => 'creative',
            'image' => 'images/apie_mus_kurybiniai_sprendimai.jpg',
            'image_position' => 'right',
            'items' => [
                ['key' => 'animation_production', 'icon' => 'play'],
                ['key' => 'motion_graphics', 'icon' => 'clapperboard'],
                ['key' => 'social_media_visual_content', 'icon' => 'share-2'],
                ['key' => 'advertising_design', 'icon' => 'megaphone'],
                ['key' => 'brand_visual_identity', 'icon' => 'palette'],
                ['key' => 'ai_solutions_integration', 'icon' => 'brain-circuit'],
            ],
        ],
    ];

    $aboutStats = [
        ['number' => '10+', 'key' => 'experience', 'icon' => 'package'],
        ['number' => '100+', 'key' => 'projects', 'icon' => 'blocks'],
        ['number' => '50+', 'key' => 'clients', 'icon' => 'users'],
        ['number' => '15+', 'key' => 'technologies', 'icon' => 'layers'],
    ];
@endphp

<section class="about-page">
    <div class="about-page__container">
        <section class="about-page__hero">
            <div class="about-page__hero-copy">
                <p class="about-page__eyebrow">{{ __('messages.about.hero_eyebrow') }}</p>
                <h1 class="about-page__title">{{ __('messages.about.hero_title') }}</h1>
                <p class="about-page__lead">{{ __('messages.about.lead') }}</p>

                <div class="about-page__hero-actions">
                    <a href="{{ $url::route('contact') }}" class="about-page__primary-button">
                        {{ __('messages.about.contact_cta') }}
                    </a>
                </div>
            </div>

            <figure class="about-page__portrait">
                <img
                    src="{{ asset('images/profile/edenas-pocius.jpg') }}"
                    alt="Edenas Pocius profile photo"
                    class="about-page__portrait-image"
                >
            </figure>
        </section>

        <section class="about-page__stats" aria-label="{{ __('messages.about.stats_label') }}">
            @foreach ($aboutStats as $stat)
                <article class="about-page__stat-item">
                    <span class="about-page__stat-icon" aria-hidden="true">
                        @if ($stat['icon'] === 'users')
                            <svg viewBox="0 0 24 24" focusable="false">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        @else
                            <i data-lucide="{{ $stat['icon'] }}"></i>
                        @endif
                    </span>
                    <span class="about-page__stat-number">{{ $stat['number'] }}</span>
                    <span class="about-page__stat-label">{{ __('messages.about.stats.'.$stat['key']) }}</span>
                </article>
            @endforeach
        </section>

        <div class="about-page__main-grid">
            <article class="about-page__card about-page__text-panel split-card">
                <div class="about-page__text-content split-card__content">
                    <p class="about-page__text-eyebrow">{{ __('messages.about.title') }}</p>
                    <h2 class="about-page__text-title">{{ __('messages.about.hero_title') }}</h2>
                    <span class="about-page__text-divider" aria-hidden="true"></span>

                    <div class="about-page__text-body">
                        @foreach (__('messages.about.paragraphs') as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </div>

                <figure class="about-page__text-media split-card__media">
                    <img
                        src="{{ asset('images/apie_mane_info.jpg') }}"
                        alt="{{ __('messages.about.hero_title') }}"
                        class="about-page__text-image split-card__image"
                    >
                </figure>
            </article>
        </div>

        <section class="about-page__services-panel" aria-labelledby="about-services-title">
            <div class="about-page__services-header">
                <p class="about-page__services-eyebrow">{{ __('messages.about.services') }}</p>
                <h2 class="about-page__services-title" id="about-services-title">{{ __('messages.about.services_title') }}</h2>
                <p class="about-page__services-intro">{{ __('messages.about.services_intro') }}</p>
            </div>

            <div class="about-page__service-blocks">
                @foreach ($aboutServiceGroups as $group)
                    <article class="about-page__service-block about-page__service-block--image-{{ $group['image_position'] }} split-card">
                        <figure class="about-page__service-media split-card__media">
                            <img
                                src="{{ asset($group['image']) }}"
                                alt="{{ __('messages.about.service_groups.'.$group['key'].'.image_alt') }}"
                                class="about-page__service-image split-card__image"
                            >
                        </figure>

                        <div class="about-page__service-content split-card__content">
                            <h3 class="about-page__service-heading">{{ __('messages.about.service_groups.'.$group['key'].'.title') }}</h3>
                            <span class="about-page__service-heading-line" aria-hidden="true"></span>

                            <ul class="about-page__service-list">
                                @foreach ($group['items'] as $item)
                                    <li class="about-page__service-row">
                                        <span class="about-page__service-icon" aria-hidden="true">
                                            <i data-lucide="{{ $item['icon'] }}"></i>
                                        </span>
                                        <span class="about-page__service-copy">
                                            <span class="about-page__service-name">{{ __('messages.about.service_items.'.$item['key']) }}</span>
                                            <span class="about-page__service-text">{{ __('messages.about.service_descriptions.'.$item['key']) }}</span>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="about-page__card about-page__links-card" aria-label="{{ __('messages.about.links') }}">
            <div class="about-page__section-header">
                <h2 class="about-page__section-title">{{ __('messages.about.links') }}</h2>
                <p class="about-page__section-subtitle">{{ __('messages.about.links_subtitle') }}</p>
            </div>

            <div class="about-page__social-grid">
                <a href="https://github.com/edenas" class="about-page__social-link" target="_blank" rel="noopener noreferrer">
                    <svg class="about-page__social-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2.25A9.75 9.75 0 0 0 8.92 21.26c.49.09.67-.21.67-.47v-1.7c-2.73.59-3.31-1.17-3.31-1.17-.45-1.14-1.1-1.44-1.1-1.44-.9-.61.07-.6.07-.6.99.07 1.52 1.02 1.52 1.02.89 1.51 2.33 1.07 2.9.82.09-.64.35-1.07.63-1.32-2.18-.25-4.47-1.09-4.47-4.85 0-1.07.38-1.95 1.02-2.64-.1-.25-.44-1.25.1-2.6 0 0 .83-.27 2.72 1.01A9.34 9.34 0 0 1 12 6.99c.84 0 1.69.11 2.48.33 1.89-1.28 2.72-1.01 2.72-1.01.54 1.35.2 2.35.1 2.6.63.69 1.01 1.57 1.01 2.64 0 3.77-2.3 4.6-4.48 4.85.36.31.67.91.67 1.84v2.55c0 .26.18.57.68.47A9.75 9.75 0 0 0 12 2.25Z" />
                    </svg>
                    <span>GitHub</span>
                </a>

                <a href="https://www.linkedin.com/in/edenas-pocius-0b4a59191/" class="about-page__social-link" target="_blank" rel="noopener noreferrer">
                    <svg class="about-page__social-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6.94 8.75H3.56v10.69h3.38V8.75ZM5.25 4.56a1.96 1.96 0 1 0 0 3.92 1.96 1.96 0 0 0 0-3.92Zm13.97 8.76c0-3.22-1.72-4.72-4.02-4.72a3.48 3.48 0 0 0-3.13 1.72V8.75H8.83v10.69h3.37v-5.29c0-1.4.27-2.75 2-2.75 1.7 0 1.72 1.59 1.72 2.84v5.2h3.38l-.08-6.12Z" />
                    </svg>
                    <span>LinkedIn</span>
                </a>
            </div>
        </section>

        <section class="about-page__card about-page__tech-panel" aria-labelledby="about-tech-title">
            <h2 class="about-page__section-title" id="about-tech-title">{{ __('messages.about.tech') }}</h2>
            <div class="about-page__tech-list">
                @foreach ($aboutTechItems as $item)
                    <article class="about-page__list-row">
                        <i data-lucide="{{ $item['icon'] }}" class="about-page__row-icon" aria-hidden="true"></i>
                        <span>{{ $item['label'] }}</span>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</section>
@endsection
