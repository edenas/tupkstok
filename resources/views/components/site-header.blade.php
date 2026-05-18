<header class="site-header">
    <div class="site-header__inner">
        <a href="{{ url('/') }}" class="site-header__brand" aria-label="EPgalerija home">
            <img src="{{ asset('images/logo.png') }}" alt="EPgalerija logo" class="site-header__logo">
        </a>
        <nav class="site-header__nav" id="site-header-menu" aria-label="Main navigation" data-site-menu>
            <ul class="site-header__nav-list">
                <li><a href="{{ url('/') }}" class="{{ request()->is('/') ? 'site-header__nav-link--active' : '' }}">{{ __('messages.nav.home') }}</a></li>
                <li><a href="{{ route('about-me') }}" class="{{ request()->routeIs('about-me') ? 'site-header__nav-link--active' : '' }}">{{ __('messages.nav.about') }}</a></li>
                <li><a href="{{ route('web-solutions') }}" class="{{ request()->routeIs('web-solutions') ? 'site-header__nav-link--active' : '' }}">{{ __('messages.nav.web_solutions') }}</a></li>
                <li><a href="{{ route('mobile-apps') }}" class="{{ request()->routeIs('mobile-apps') ? 'site-header__nav-link--active' : '' }}">{{ __('messages.nav.mobile_apps') }}</a></li>
                <li><a href="{{ route('graphics') }}" class="{{ request()->routeIs('graphics*') ? 'site-header__nav-link--active' : '' }}">{{ __('messages.nav.graphics') }}</a></li>
                <li><a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'site-header__nav-link--active' : '' }}">{{ __('messages.nav.contact') }}</a></li>
            </ul>
        </nav>
        <div class="site-header__language-switcher" aria-label="Language">
            <a href="{{ route('language.switch', 'lt') }}" class="language-flag {{ app()->getLocale() === 'lt' ? 'language-flag--active' : '' }}" title="{{ __('messages.languages.lt') }}" aria-label="Lithuanian">
                <img src="{{ asset('images/flags/lt.svg') }}" alt="Lithuanian" class="language-flag__image">
            </a>
            <a href="{{ route('language.switch', 'en') }}" class="language-flag {{ app()->getLocale() === 'en' ? 'language-flag--active' : '' }}" title="{{ __('messages.languages.en') }}" aria-label="English">
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
