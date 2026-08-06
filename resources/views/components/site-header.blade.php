<header class="site-header">
    <div class="site-header__inner">
        <a href="{{ route('home') }}" class="site-header__brand" aria-label="Pradžia">
            <img src="{{ asset('storage/logo.png') }}" alt="Tupk Stok" class="site-header__logo">
        </a>
        <nav class="site-header__nav" id="site-header-menu" aria-label="Pagrindinė navigacija" data-site-menu>
            <ul class="site-header__nav-list">
                <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'site-header__nav-link--active' : '' }}">{{ __('messages.nav.home') }}</a></li>
                <li><a href="{{ route('blog') }}" class="{{ request()->routeIs('blog*') ? 'site-header__nav-link--active' : '' }}">{{ __('messages.nav.blog') }}</a></li>
                <li><a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'site-header__nav-link--active' : '' }}">{{ __('messages.nav.contact') }}</a></li>
            </ul>
        </nav>
        <button
            type="button"
            class="site-header__menu-toggle"
            data-site-menu-toggle
            aria-controls="site-header-menu"
            aria-expanded="false"
            aria-label="Atidaryti navigacijos meniu"
        >
            <span class="site-header__menu-line"></span>
            <span class="site-header__menu-line"></span>
            <span class="site-header__menu-line"></span>
        </button>
    </div>
</header>
