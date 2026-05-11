<header class="admin-topbar">
    <div class="admin-topbar__spacer" aria-hidden="true"></div>

    <div class="admin-topbar__user">
        <span class="admin-topbar__user-name">{{ auth()->user()->name }}</span>
        <span class="admin-topbar__dropdown-indicator" aria-hidden="true">
            <svg viewBox="0 0 20 20" focusable="false">
                <path fill="currentColor" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" />
            </svg>
        </span>
    </div>
</header>
