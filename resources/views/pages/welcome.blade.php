@extends('layouts.app')

@section('content')
@php
    $services = [
        [
            'key' => 'web_solutions',
            'icon' => 'monitor-smartphone',
            'url' => route('web-solutions'),
        ],
        [
            'key' => 'mobile_apps',
            'icon' => 'smartphone',
            'url' => route('mobile-apps'),
        ],
        [
            'key' => 'graphic_design',
            'icon' => 'pen-tool',
            'url' => route('graphics'),
        ],
        [
            'key' => 'seo_optimization',
            'icon' => 'search',
            'url' => route('web-solutions'),
        ],
        [
            'key' => 'technologies',
            'icon' => 'code',
            'url' => route('about-me'),
        ],
    ];

    $contacts = [
        [
            'key' => 'location',
            'url' => null,
            'icon' => '<path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        ],
        [
            'key' => 'phone',
            'value' => '+370 609 60686',
            'url' => 'tel:+37060960686',
            'icon' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.32 1.77.59 2.61a2 2 0 0 1-.45 2.11L8 9.7a16 16 0 0 0 6.3 6.3l1.26-1.25a2 2 0 0 1 2.11-.45c.84.27 1.71.47 2.61.59A2 2 0 0 1 22 16.92Z"/>',
        ],
        [
            'key' => 'email',
            'value' => 'edenas.pocius@gmail.com',
            'url' => 'mailto:edenas.pocius@gmail.com',
            'icon' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 7 9-7"/>',
        ],
        [
            'key' => 'messenger',
            'url' => 'https://m.me/edenas.pocius.1',
            'external' => true,
            'icon' => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7A8.38 8.38 0 0 1 4 11.5a8.5 8.5 0 0 1 17 0Z"/><path d="m8 13 2.5-2.5 2 2L16 9"/>',
        ],
    ];

    $principles = [
        [
            'key' => 'strategic_thinking',
            'icon' => 'target',
        ],
        [
            'key' => 'clean_code',
            'icon' => 'code',
        ],
        [
            'key' => 'user_experience',
            'icon' => 'monitor-smartphone',
        ],
        [
            'key' => 'results',
            'icon' => 'chart-column',
        ],
        [
            'key' => 'innovation',
            'icon' => 'sparkles',
        ],
    ];
@endphp

<section class="home-page" aria-label="EPgalerija homepage">
    <div class="home-page__container">
        <section class="home-page__hero">
            <div class="home-page__hero-copy">
                <p class="home-page__eyebrow">{{ __('messages.home.hero.eyebrow') }}</p>
                <h1 class="home-page__title">{{ __('messages.home.hero.title') }}</h1>
                <p class="home-page__lead">
                    {{ __('messages.home.hero.lead') }}
                </p>

                <div class="home-page__hero-actions">
                    <a href="#paslaugos" class="home-page__button home-page__button--primary">
                        {{ __('messages.home.hero.services_cta') }}
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                    </a>
                    <a href="{{ route('contact') }}" class="home-page__button home-page__button--secondary">
                        {{ __('messages.home.contact_cta') }}
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
                    </a>
                </div>
            </div>

            <figure class="home-page__hero-visual">
                <img src="{{ asset('images/hero.png') }}" alt="{{ __('messages.home.hero.image_alt') }}" class="home-page__hero-image">
            </figure>
        </section>

        <section class="home-page__services" id="paslaugos" aria-label="{{ __('messages.home.services_label') }}">
            @foreach ($services as $service)
                <article class="home-page__service-card">
                    <span class="home-page__icon" aria-hidden="true">
                        <i data-lucide="{{ $service['icon'] }}"></i>
                    </span>
                    <h2>{{ __('messages.home.services.'.$service['key'].'.title') }}</h2>
                    <p>{{ __('messages.home.services.'.$service['key'].'.text') }}</p>
                    <a href="{{ $service['url'] }}">{{ __('messages.home.learn_more') }} <span aria-hidden="true">&rarr;</span></a>
                </article>
            @endforeach
        </section>

        <section class="home-page__about-cta" aria-label="{{ __('messages.home.about.section_label') }}">
            <article class="home-page__about-card">
                <figure class="home-page__profile">
                    <img src="{{ asset('images/profile/edenas-pocius.jpg') }}" alt="Edenas Pocius">
                </figure>
                <div class="home-page__about-copy">
                    <p class="home-page__small-label">{{ __('messages.home.about.eyebrow') }}</p>
                    <h2>{{ __('messages.home.about.title') }}</h2>
                    <p>
                        {{ __('messages.home.about.text') }}
                    </p>
                    <a href="{{ route('about-me') }}" class="home-page__text-button">
                        {{ __('messages.home.about.cta') }}
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                    </a>
                </div>
            </article>

            <article class="home-page__project-card">
                <div class="home-page__project-copy">
                    <h2>{{ __('messages.home.project_cta.title') }}</h2>
                    <p>{{ __('messages.home.project_cta.text') }}</p>
                    <a href="{{ route('contact') }}" class="home-page__project-button">
                        {{ __('messages.home.contact_cta') }}
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                    </a>
                </div>
                <svg class="home-page__project-mark" viewBox="0 0 220 150" aria-hidden="true" focusable="false">
                    <path d="M38 104c38 22 92 9 121-36"/>
                    <path d="m151 27 47-16-16 48-14-19-24 23"/>
                </svg>
            </article>
        </section>

        <section class="home-page__contact-strip" aria-label="{{ __('messages.home.contact.label') }}">
            @foreach ($contacts as $contact)
                <{{ $contact['url'] ? 'a' : 'div' }}
                    @if ($contact['url'])
                        href="{{ $contact['url'] }}"
                        @if (!empty($contact['external'])) target="_blank" rel="noopener noreferrer" @endif
                    @endif
                    class="home-page__contact-item"
                >
                    <span class="home-page__contact-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" focusable="false">{!! $contact['icon'] !!}</svg>
                    </span>
                    <span class="home-page__contact-text">
                        <span>{{ __('messages.home.contact.items.'.$contact['key'].'.label') }}</span>
                        <strong>{{ $contact['value'] ?? __('messages.home.contact.items.'.$contact['key'].'.value') }}</strong>
                    </span>
                </{{ $contact['url'] ? 'a' : 'div' }}>
            @endforeach
        </section>

        <section class="home-page__principles" aria-labelledby="principles-title">
            <div class="home-page__principles-panel">
                <figure class="home-page__principles-visual">
                    <img src="{{ asset('images/darbo_principai.jpg') }}" alt="{{ __('messages.home.principles.title') }}">
                </figure>

                <div class="home-page__principles-content">
                    <div class="home-page__section-heading">
                        <p class="home-page__eyebrow">{{ __('messages.home.principles.eyebrow') }}</p>
                        <h2 id="principles-title">{{ __('messages.home.principles.title') }}</h2>
                    </div>

                    <div class="home-page__principles-list">
                        @foreach ($principles as $principle)
                            <article class="home-page__principle-card">
                                <span class="home-page__icon" aria-hidden="true">
                                    <i data-lucide="{{ $principle['icon'] }}"></i>
                                </span>
                                <div class="home-page__principle-copy">
                                    <h3>{{ __('messages.home.principles.items.'.$principle['key'].'.title') }}</h3>
                                    <p>{{ __('messages.home.principles.items.'.$principle['key'].'.text') }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>
</section>
@endsection
