@extends('layouts.app')

@section('content')
@php
    $webServices = [
        ['key' => 'Website development', 'icon' => 'website'],
        ['key' => 'Web application development', 'icon' => 'app'],
        ['key' => 'Front-End development', 'icon' => 'code'],
        ['key' => 'Responsive design', 'icon' => 'responsive'],
        ['key' => 'UI/UX design', 'icon' => 'design'],
        ['key' => 'Laravel development', 'icon' => 'laravel'],
        ['key' => 'WordPress solutions', 'icon' => 'wordpress'],
        ['key' => 'Performance optimization', 'icon' => 'performance'],
        ['key' => 'SEO basics integration', 'icon' => 'seo'],
        ['key' => 'AI solutions integration in web projects', 'icon' => 'ai'],
    ];

    $serviceIcons = [
        'website' => '<path d="M4 5.5h16v11H4z"/><path d="M4 9h16"/><path d="M7 7.25h.01M9.5 7.25h.01"/>',
        'app' => '<rect x="5" y="4" width="14" height="16" rx="2"/><path d="M8 8h8M8 12h8M8 16h4"/>',
        'code' => '<path d="m9 8-4 4 4 4"/><path d="m15 8 4 4-4 4"/><path d="m13 6-2 12"/>',
        'responsive' => '<rect x="3" y="5" width="13" height="10" rx="2"/><rect x="17" y="9" width="4" height="9" rx="1"/><path d="M8 19h3"/>',
        'design' => '<path d="M12 4v16"/><path d="M6 8h12"/><path d="M7 16c2-4 8-4 10 0"/>',
        'laravel' => '<path d="M5 5v11l7 4 7-4V5l-7 4z"/><path d="M5 5l7 4 7-4"/><path d="M12 9v11"/>',
        'wordpress' => '<circle cx="12" cy="12" r="8"/><path d="M6.8 8h2.4l2.1 7.1L13 10l-.7-2h2.2l2.1 7.1"/><path d="M16.8 8.8c.5.8.8 1.9.8 3.2"/>',
        'performance' => '<path d="M4 14a8 8 0 1 1 16 0"/><path d="M12 14l4-5"/><path d="M8 18h8"/>',
        'seo' => '<circle cx="10.5" cy="10.5" r="5.5"/><path d="m15 15 4 4"/><path d="M8.5 10.5h4M10.5 8.5v4"/>',
        'ai' => '<path d="M12 4v3M12 17v3M4 12h3M17 12h3"/><rect x="7" y="7" width="10" height="10" rx="2"/><path d="M10 13.5 11.2 10h1.6l1.2 3.5M10.5 12.4h3"/>',
    ];

    $projects = [
        [
            'domain' => 'eadvokatai.lt',
            'client_key' => 'eadvokatai',
            'url' => 'https://eadvokatai.lt/',
            'logo' => 'eadvokatai.jpg',
        ],
        [
            'domain' => 'gardamas.lt',
            'client_key' => 'gardamas',
            'url' => 'https://gardamas.lt/',
            'logo' => 'gardamas.jpg',
        ],
        [
            'domain' => 'saulesmainai.lt',
            'client_key' => 'saulesmainai',
            'url' => 'https://saulesmainai.lt/',
            'logo' => 'saulesmainai.jpg',
        ],
        [
            'domain' => 'tupkstok.lt',
            'client_key' => 'tupkstok',
            'url' => 'https://tupkstok.lt/',
            'logo' => 'tupkstok.jpg',
        ],
        [
            'domain' => 'sleepangel.lt',
            'client_key' => 'sleepangel',
            'url' => 'https://sleepangel.lt/',
            'logo' => 'sleepangel.jpg',
        ],
    ];

    $featureItems = [
        ['key' => 'modern_design', 'icon' => 'design'],
        ['key' => 'performance', 'icon' => 'code'],
        ['key' => 'seo_integration', 'icon' => 'seo'],
        ['key' => 'ai_solutions', 'icon' => 'ai'],
    ];
@endphp

<section class="web-solutions-page">
    <div class="web-solutions-page__container">
        <div class="web-solutions-page__top-grid">
            <header class="web-solutions-page__hero">
                <p class="web-solutions-page__eyebrow">{{ __('messages.web.eyebrow') }}</p>
                <h1 class="web-solutions-page__title">{{ __('messages.web.title') }}</h1>
                <p class="web-solutions-page__lead">
                    {{ __('messages.web.lead') }}
                </p>
            </header>

            <section class="web-solutions-page__section web-solutions-page__section--services">
                <div class="web-solutions-page__section-header">
                    <h2>{{ __('messages.common.services') }}</h2>
                    <p>{{ __('messages.web.services_intro') }}</p>
                </div>

                <div class="web-solutions-page__services-list">
                    @foreach ($webServices as $service)
                        <article class="web-solutions-page__service-row">
                            <span class="web-solutions-page__service-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" focusable="false">
                                    {!! $serviceIcons[$service['icon']] !!}
                                </svg>
                            </span>
                            <h3>{{ __('messages.web.services.'.$service['key']) }}</h3>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>

        <section class="web-solutions-page__feature-panel" aria-label="{{ __('messages.web.feature_strip_label') }}">
            <figure class="web-solutions-page__feature-visual">
                <img src="{{ asset('images/web_services.jpg') }}" alt="{{ __('messages.web.feature_strip_label') }}">
            </figure>

            <div class="web-solutions-page__feature-content">
                <div class="web-solutions-page__feature-heading">
                    <p class="web-solutions-page__eyebrow">{{ app()->getLocale() === 'lt' ? 'KODĖL VERTA DIRBTI KARTU?' : 'WHY WORK TOGETHER?' }}</p>
                    <h2>{{ app()->getLocale() === 'lt' ? 'Web sprendimai, kurie veikia' : 'Web solutions that work' }}</h2>
                </div>

                <div class="web-solutions-page__feature-list">
                    @foreach ($featureItems as $feature)
                        <article class="web-solutions-page__feature-item">
                            <span class="web-solutions-page__feature-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" focusable="false">
                                    {!! $serviceIcons[$feature['icon']] !!}
                                </svg>
                            </span>
                            <div class="web-solutions-page__feature-copy">
                                <h3>{{ __('messages.web.feature_strip.'.$feature['key'].'.title') }}</h3>
                                <p>{{ __('messages.web.feature_strip.'.$feature['key'].'.text') }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="web-solutions-page__section">
            <div class="web-solutions-page__section-header">
                <h2>{{ __('messages.common.completed_projects') }}</h2>
                <p>{{ __('messages.web.projects_intro') }}</p>
            </div>

            <div class="web-solutions-page__projects-list">
                @foreach ($projects as $project)
                    <article class="web-solutions-page__project-row">
                        @php($clientTitle = __('messages.web.clients.'.$project['client_key'].'.title'))
                        <a href="{{ $project['url'] }}" target="_blank" rel="noopener noreferrer" class="web-solutions-page__logo-frame" aria-label="{{ __('messages.common.view_project') }}: {{ $clientTitle }}">
                            <span class="web-solutions-page__logo-fallback">{{ $clientTitle }}</span>
                            <img
                                src="{{ asset('storage/clients/'.$project['logo']) }}"
                                alt="{{ $clientTitle }} logo"
                                loading="lazy"
                                onload="this.previousElementSibling.hidden = true;"
                                onerror="this.hidden = true;"
                            >
                        </a>

                        <div class="web-solutions-page__project-content">
                            <a href="{{ $project['url'] }}" target="_blank" rel="noopener noreferrer" class="web-solutions-page__project-domain" aria-label="{{ __('messages.common.view_project') }}: {{ $clientTitle }}">
                                {{ $project['domain'] }}
                            </a>
                            <h3>{{ $clientTitle }}</h3>
                            <div class="web-solutions-page__project-description">
                                <p>{{ __('messages.web.clients.'.$project['client_key'].'.description') }}</p>
                            </div>
                        </div>

                        <a href="{{ $project['url'] }}" target="_blank" rel="noopener noreferrer" class="web-solutions-page__project-link" aria-label="{{ __('messages.common.view_project') }}: {{ $clientTitle }}">
                            <svg class="web-solutions-page__project-link-arrow" viewBox="0 0 24 24" focusable="false" aria-hidden="true">
                                <path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>
                            </svg>
                        </a>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</section>
@endsection
