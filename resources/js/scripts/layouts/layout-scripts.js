export function initializeLayoutScripts() {
    const button = document.querySelector('[data-back-to-top]');
    const footer = document.querySelector('.site-footer');
    const header = document.querySelector('.site-header');
    const menuToggle = document.querySelector('[data-site-menu-toggle]');
    const menu = document.querySelector('[data-site-menu]');
    const desktopQuery = window.matchMedia('(min-width: 1024px)');
    const loadingOverlay = document.querySelector('[data-public-loading-overlay]');
    const loadingDelay = 320;
    let loadingTimer = null;

    const hidePublicLoader = () => {
        if (loadingTimer) {
            window.clearTimeout(loadingTimer);
            loadingTimer = null;
        }

        document.body.classList.remove('public-body--page-leaving');
        loadingOverlay?.classList.remove('public-loading-overlay--visible');
    };

    const showPublicLoaderAfterDelay = () => {
        if (!loadingOverlay || loadingTimer) {
            return;
        }

        loadingTimer = window.setTimeout(() => {
            loadingOverlay.classList.add('public-loading-overlay--visible');
        }, loadingDelay);
    };

    const isPublicNavigationLink = (link, event) => {
        if (!link || event.defaultPrevented || event.button !== 0) {
            return false;
        }

        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return false;
        }

        if (link.target && link.target.toLowerCase() !== '_self') {
            return false;
        }

        if (link.hasAttribute('download') || link.dataset.noPageTransition !== undefined) {
            return false;
        }

        const href = link.getAttribute('href');

        if (!href || href.startsWith('#')) {
            return false;
        }

        const url = new URL(link.href, window.location.href);

        if (url.origin !== window.location.origin) {
            return false;
        }

        if (['mailto:', 'tel:'].includes(url.protocol)) {
            return false;
        }

        if (url.pathname.startsWith('/admin')) {
            return false;
        }

        if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) {
            return false;
        }

        return url.href !== window.location.href || url.hash === '';
    };

    if (document.body.classList.contains('public-body')) {
        document.addEventListener('click', (event) => {
            const link = event.target.closest('a');

            if (!isPublicNavigationLink(link, event)) {
                return;
            }

            document.body.classList.add('public-body--page-leaving');
            showPublicLoaderAfterDelay();
        });

        window.addEventListener('pageshow', hidePublicLoader);
    }

    if (header && menuToggle && menu) {
        const setMenuOpen = (isOpen) => {
            header.classList.toggle('site-header--menu-open', isOpen);
            document.body.classList.toggle('public-body--menu-open', isOpen);
            menuToggle.setAttribute('aria-expanded', String(isOpen));
            menuToggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
        };

        menuToggle.addEventListener('click', () => {
            setMenuOpen(!header.classList.contains('site-header--menu-open'));
        });

        menu.addEventListener('click', (event) => {
            if (event.target.closest('a')) {
                setMenuOpen(false);
            }
        });

        document.addEventListener('click', (event) => {
            if (!header.classList.contains('site-header--menu-open')) {
                return;
            }

            if (!header.contains(event.target)) {
                setMenuOpen(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setMenuOpen(false);
            }
        });

        desktopQuery.addEventListener('change', (event) => {
            if (event.matches) {
                setMenuOpen(false);
            }
        });
    }

    if (!button) {
        return;
    }

    const defaultOffset = () => (window.matchMedia('(max-width: 640px)').matches ? 18 : 28);
    const footerGap = () => (window.matchMedia('(max-width: 640px)').matches ? 18 : 26);

    const updateButton = () => {
        const shouldShow = window.scrollY > 360;
        button.classList.toggle('back-to-top--visible', shouldShow);

        if (!footer) {
            button.style.removeProperty('--back-to-top-bottom');
            return;
        }

        const footerRect = footer.getBoundingClientRect();
        const overlap = Math.max(0, window.innerHeight - footerRect.top);
        const bottomOffset = overlap > 0 ? overlap + footerGap() : defaultOffset();

        button.style.setProperty('--back-to-top-bottom', `${bottomOffset}px`);
    };

    let ticking = false;
    const requestUpdate = () => {
        if (ticking) {
            return;
        }

        ticking = true;
        window.requestAnimationFrame(() => {
            updateButton();
            ticking = false;
        });
    };

    button.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth',
        });
    });

    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', requestUpdate);
    updateButton();
}
