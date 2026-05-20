<header class="site-header">
    @php($url = \App\Support\LocalizedUrl::class)
    <div class="site-header__inner">
        <a href="{{ $url::route('home') }}" class="site-header__brand" aria-label="EPgalerija home">
            <img src="{{ asset('images/logo.png') }}" alt="EPgalerija logo" class="site-header__logo">
        </a>
        <nav class="site-header__nav" id="site-header-menu" aria-label="Main navigation" data-site-menu>
            <ul class="site-header__nav-list">
                <li><a href="{{ $url::route('home') }}" class="{{ $url::active('home') ? 'site-header__nav-link--active' : '' }}">{{ __('messages.nav.home') }}</a></li>
                <li><a href="{{ $url::route('about-me') }}" class="{{ $url::active('about-me') ? 'site-header__nav-link--active' : '' }}">{{ __('messages.nav.about') }}</a></li>
                <li><a href="{{ $url::route('web-solutions') }}" class="{{ $url::active('web-solutions') ? 'site-header__nav-link--active' : '' }}">{{ __('messages.nav.web_solutions') }}</a></li>
                <li><a href="{{ $url::route('mobile-apps') }}" class="{{ $url::active('mobile-apps') ? 'site-header__nav-link--active' : '' }}">{{ __('messages.nav.mobile_apps') }}</a></li>
                <li><a href="{{ $url::route('graphics') }}" class="{{ $url::active('graphics') ? 'site-header__nav-link--active' : '' }}">{{ __('messages.nav.graphics') }}</a></li>
                <li><a href="{{ $url::route('contact') }}" class="{{ $url::active('contact') ? 'site-header__nav-link--active' : '' }}">{{ __('messages.nav.contact') }}</a></li>
            </ul>
            <div class="site-header__mobile-socials" aria-label="Contact links">
                <a href="https://www.linkedin.com/in/edenas-pocius-0b4a59191/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6.94 8.75H3.56v10.69h3.38V8.75ZM5.25 4.56a1.96 1.96 0 1 0 0 3.92 1.96 1.96 0 0 0 0-3.92Zm13.97 8.76c0-3.22-1.72-4.72-4.02-4.72a3.48 3.48 0 0 0-3.13 1.72V8.75H8.83v10.69h3.37v-5.29c0-1.4.27-2.75 2-2.75 1.7 0 1.72 1.59 1.72 2.84v5.2h3.38l-.08-6.12Z"/></svg>
                </a>
                <a href="{{ $url::route('graphics') }}" aria-label="Portfolio">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.2 2.46 3.43 5.64 3.43 9S14.2 18.54 12 21M12 3c-2.2 2.46-3.43 5.64-3.43 9S9.8 18.54 12 21"/></svg>
                </a>
                <a href="mailto:edenas.pocius@gmail.com" aria-label="Email">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 7 9-7"/></svg>
                </a>
            </div>
        </nav>
        <div class="site-header__language-switcher" aria-label="Language">
            <a href="{{ $url::current('lt') }}" class="language-flag {{ app()->getLocale() === 'lt' ? 'language-flag--active' : '' }}" title="{{ __('messages.languages.lt') }}" aria-label="Lithuanian">
                <img src="{{ asset('images/flags/lt.svg') }}" alt="Lithuanian" class="language-flag__image">
            </a>
            <a href="{{ $url::current('en') }}" class="language-flag {{ app()->getLocale() === 'en' ? 'language-flag--active' : '' }}" title="{{ __('messages.languages.en') }}" aria-label="English">
                <img src="{{ asset('images/flags/gb.svg') }}" alt="English" class="language-flag__image">
            </a>
        </div>
        <button
            type="button"
            class="site-header__menu-toggle"
            data-site-menu-toggle
            aria-controls="site-header-menu"
            aria-expanded="false"
            aria-label="Open navigation menu"
        >
            <span class="site-header__menu-line"></span>
            <span class="site-header__menu-line"></span>
            <span class="site-header__menu-line"></span>
        </button>
    </div>
</header>
