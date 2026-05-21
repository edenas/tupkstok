<aside class="admin-sidebar">
    <div class="admin-sidebar__brand">
        <img src="{{ asset('images/logo_white.png') }}" alt="EPgalerija" class="admin-sidebar__logo">
    </div>

    <nav class="admin-sidebar__navigation" aria-label="Admin navigation">
        <a href="{{ route('admin.dashboard') }}" class="admin-sidebar__link {{ request()->routeIs('admin.dashboard') ? 'admin-sidebar__link--active' : '' }}">
            {{ __('messages.admin.dashboard') }}
        </a>
        <a href="{{ route('admin.statistics') }}" class="admin-sidebar__link {{ request()->routeIs('admin.statistics') ? 'admin-sidebar__link--active' : '' }}">
            {{ __('messages.admin.statistics.nav') }}
        </a>
        <a href="{{ route('admin.seo.edit') }}" class="admin-sidebar__link {{ request()->routeIs('admin.seo*') ? 'admin-sidebar__link--active' : '' }}">
            {{ __('messages.admin.seo.nav') }}
        </a>
        <a href="{{ route('admin.users') }}" class="admin-sidebar__link {{ request()->routeIs('admin.users*') ? 'admin-sidebar__link--active' : '' }}">
            {{ __('messages.admin.users') }}
        </a>
        <a href="{{ route('admin.portfolio') }}" class="admin-sidebar__link {{ request()->routeIs('admin.portfolio*') ? 'admin-sidebar__link--active' : '' }}">
            {{ __('messages.admin.portfolio') }}
        </a>
    </nav>

    <div class="admin-sidebar__footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="admin-sidebar__logout-button">
                {{ __('messages.admin.logout') }}
            </button>
        </form>
    </div>
</aside>
