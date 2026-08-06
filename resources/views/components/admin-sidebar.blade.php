<aside class="admin-sidebar">
    <div class="admin-sidebar__brand">
        <img src="{{ asset('storage/logo_white.png') }}" alt="Tupk Stok" class="admin-sidebar__logo">
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
        <a href="{{ route('admin.media-library') }}" class="admin-sidebar__link {{ request()->routeIs('admin.media-library*') ? 'admin-sidebar__link--active' : '' }}">
            Failų saugykla
        </a>
        <a href="{{ route('admin.users') }}" class="admin-sidebar__link {{ request()->routeIs('admin.users*') ? 'admin-sidebar__link--active' : '' }}">
            {{ __('messages.admin.users') }}
        </a>
        <a href="{{ route('admin.blog') }}" class="admin-sidebar__link {{ request()->routeIs('admin.blog*') ? 'admin-sidebar__link--active' : '' }}">
            {{ __('messages.admin.blog') }}
        </a>
        <a href="{{ route('admin.wordpress-migration.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.wordpress-migration.*') ? 'admin-sidebar__link--active' : '' }}">
            WordPress importavimo centras
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

