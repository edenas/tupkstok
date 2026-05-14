<header class="site-header">
    <div class="site-header__inner">
        <a href="{{ url('/') }}" class="site-header__brand" aria-label="EPgalerija home">
            <img src="{{ asset('images/logo.png') }}" alt="EPgalerija logo" class="site-header__logo">
        </a>
        <nav aria-label="Main navigation">
            <ul class="site-header__nav-list">
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><a href="{{ route('about-me') }}">About me</a></li>
                <li><a href="{{ route('web-solutions') }}">Web Solutions</a></li>
                <li><a href="{{ route('mobile-apps') }}">Mobile Apps</a></li>
                <li><a href="{{ route('graphics') }}">Graphics</a></li>
                <li><a href="{{ route('contact') }}">Contact</a></li>
            </ul>
        </nav>
    </div>
</header>
