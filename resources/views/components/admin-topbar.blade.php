<header class="admin-topbar">
    <div class="admin-topbar__spacer" aria-hidden="true"></div>

    <div class="admin-topbar__user">
        <div class="admin-topbar__language-switcher" aria-label="Language">
            <a href="{{ route('language.switch', 'lt') }}" class="admin-language-link {{ app()->getLocale() === 'lt' ? 'admin-language-link--active' : '' }}" aria-label="Lithuanian" title="{{ __('messages.languages.lt') }}">
                <img src="{{ asset('images/flags/lt.svg') }}" alt="Lithuanian" class="admin-language-link__flag">
            </a>
            <a href="{{ route('language.switch', 'en') }}" class="admin-language-link {{ app()->getLocale() === 'en' ? 'admin-language-link--active' : '' }}" aria-label="English" title="{{ __('messages.languages.en') }}">
                <img src="{{ asset('images/flags/gb.svg') }}" alt="English" class="admin-language-link__flag">
            </a>
            <a href="{{ route('language.switch', 'ru') }}" class="admin-language-link {{ app()->getLocale() === 'ru' ? 'admin-language-link--active' : '' }}" aria-label="Russian" title="{{ __('messages.languages.ru') }}">
                <img src="{{ asset('images/flags/ru.svg') }}" alt="Russian" class="admin-language-link__flag">
            </a>
        </div>
        <span class="admin-topbar__user-name">{{ auth()->user()->name }}</span>
    </div>
</header>
