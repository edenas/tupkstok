@extends('layouts.app')

@section('content')
<section class="about-page">
    <div class="about-page__container">
        <div class="about-page__intro">
            <figure class="about-page__portrait">
                <img
                    src="{{ asset('images/profile/edenas-pocius.jpg') }}"
                    alt="Edenas Pocius profile photo"
                    class="about-page__portrait-image"
                >
            </figure>

            <div class="about-page__intro-copy">
            <p class="about-page__eyebrow">{{ __('messages.about.eyebrow') }}</p>
                <h1 class="about-page__title">{{ __('messages.about.title') }}</h1>
                <p class="about-page__lead">
                {{ __('messages.about.lead') }}
                </p>
            </div>
        </div>

        @php
            $aboutTechItems = [
                ['label' => 'Laravel', 'icon' => 'blocks'],
                ['label' => 'PHP', 'icon' => 'braces'],
                ['label' => 'JavaScript', 'icon' => 'code'],
                ['label' => 'TypeScript', 'icon' => 'file-type'],
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

            $aboutServiceItems = [
                ['key' => 'website_development', 'icon' => 'globe'],
                ['key' => 'web_application_development', 'icon' => 'app-window'],
                ['key' => 'android_mobile_application_development', 'icon' => 'smartphone'],
                ['key' => 'ui_ux_design', 'icon' => 'pen-tool'],
                ['key' => 'front_end_development', 'icon' => 'code'],
                ['key' => 'responsive_design', 'icon' => 'monitor-smartphone'],
                ['key' => 'animation_production', 'icon' => 'play'],
                ['key' => 'motion_graphics', 'icon' => 'clapperboard'],
                ['key' => 'social_media_visual_content', 'icon' => 'share-2'],
                ['key' => 'advertising_design', 'icon' => 'megaphone'],
                ['key' => 'brand_visual_identity', 'icon' => 'palette'],
                ['key' => 'ai_solutions_integration', 'icon' => 'brain-circuit'],
            ];
        @endphp

        <div class="about-page__content-grid">
            <div class="about-page__content-main">
            <article class="about-page__text-panel">
                @foreach (__('messages.about.paragraphs') as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </article>

            <div class="about-page__side-column">
            <section class="about-page__tech-section about-page__tech-panel" aria-labelledby="about-tech-title">
                <h2 class="about-page__tech-title" id="about-tech-title">{{ __('messages.about.tech') }}</h2>
                <div class="about-page__tech-grid">
                    @foreach ($aboutTechItems as $item)
                        <a href="#" class="about-page__tech-card about-page__hover-card" aria-label="{{ $item['label'] }}">
                            <i data-lucide="{{ $item['icon'] }}" class="about-page__tech-icon" aria-hidden="true"></i>
                            <span class="about-page__tech-label">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="about-page__social-card" aria-label="Social links">
                <h2 class="about-page__social-title">{{ __('messages.about.links') }}</h2>
                <p class="about-page__social-subtitle">{{ __('messages.about.links_subtitle') }}</p>
                <div class="about-page__social-grid">
                    <a href="https://github.com/edenas" class="about-page__social-link about-page__hover-card" target="_blank" rel="noopener noreferrer">
                        <svg class="about-page__social-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 2.25A9.75 9.75 0 0 0 8.92 21.26c.49.09.67-.21.67-.47v-1.7c-2.73.59-3.31-1.17-3.31-1.17-.45-1.14-1.1-1.44-1.1-1.44-.9-.61.07-.6.07-.6.99.07 1.52 1.02 1.52 1.02.89 1.51 2.33 1.07 2.9.82.09-.64.35-1.07.63-1.32-2.18-.25-4.47-1.09-4.47-4.85 0-1.07.38-1.95 1.02-2.64-.1-.25-.44-1.25.1-2.6 0 0 .83-.27 2.72 1.01A9.34 9.34 0 0 1 12 6.99c.84 0 1.69.11 2.48.33 1.89-1.28 2.72-1.01 2.72-1.01.54 1.35.2 2.35.1 2.6.63.69 1.01 1.57 1.01 2.64 0 3.77-2.3 4.6-4.48 4.85.36.31.67.91.67 1.84v2.55c0 .26.18.57.68.47A9.75 9.75 0 0 0 12 2.25Z" />
                        </svg>
                        <span>GitHub</span>
                    </a>

                    <a href="https://www.linkedin.com/in/edenas-pocius-0b4a59191/" class="about-page__social-link about-page__hover-card" target="_blank" rel="noopener noreferrer">
                        <svg class="about-page__social-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M6.94 8.75H3.56v10.69h3.38V8.75ZM5.25 4.56a1.96 1.96 0 1 0 0 3.92 1.96 1.96 0 0 0 0-3.92Zm13.97 8.76c0-3.22-1.72-4.72-4.02-4.72a3.48 3.48 0 0 0-3.13 1.72V8.75H8.83v10.69h3.37v-5.29c0-1.4.27-2.75 2-2.75 1.7 0 1.72 1.59 1.72 2.84v5.2h3.38l-.08-6.12Z" />
                        </svg>
                        <span>LinkedIn</span>
                    </a>
                </div>
            </section>
            </div>
            </div>

            <div class="about-page__services-column">
            <aside class="about-page__services-panel">
                <h2 class="about-page__section-title">{{ __('messages.about.services') }}</h2>
                <p class="about-page__section-subtitle">{{ __('messages.about.services_subtitle') }}</p>
                <ul class="about-page__services-list">
                    @foreach ($aboutServiceItems as $item)
                        <li class="about-page__hover-card">
                            <i data-lucide="{{ $item['icon'] }}" class="about-page__service-icon" aria-hidden="true"></i>
                            <span>{{ __('messages.about.service_items.'.$item['key']) }}</span>
                        </li>
                    @endforeach
                </ul>
            </aside>
            </div>
        </div>
    </div>
</section>
@endsection
