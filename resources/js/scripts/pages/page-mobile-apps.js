export function initializeMobileAppsPageScripts() {
    const lightbox = document.querySelector('[data-mobile-screenshot-lightbox]');
    const lightboxImage = document.querySelector('[data-mobile-screenshot-lightbox-image]');
    const closeButton = document.querySelector('[data-mobile-screenshot-lightbox-close]');
    const triggers = document.querySelectorAll('[data-mobile-screenshot-lightbox-trigger]');

    if (!lightbox || !lightboxImage || !closeButton || triggers.length === 0) {
        return;
    }

    let activeTrigger = null;
    const originalParent = lightbox.parentNode;
    const originalNextSibling = lightbox.nextSibling;

    const attachLightboxToBody = () => {
        if (lightbox.parentNode !== document.body) {
            document.body.appendChild(lightbox);
        }
    };

    const restoreLightboxPosition = () => {
        if (!originalParent || lightbox.parentNode === originalParent) {
            return;
        }

        originalParent.insertBefore(lightbox, originalNextSibling);
    };

    const openLightbox = (trigger) => {
        const image = trigger.querySelector('img');
        const imageUrl = trigger.getAttribute('href');

        if (!imageUrl) {
            return;
        }

        activeTrigger = trigger;
        attachLightboxToBody();
        lightboxImage.src = imageUrl;
        lightboxImage.alt = image?.alt || trigger.getAttribute('aria-label') || '';
        lightbox.hidden = false;
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.classList.add('lightbox-open');
        document.body.classList.add('mobile-app-lightbox-open');
        closeButton.focus({ preventScroll: true });
    };

    const closeLightbox = () => {
        if (lightbox.hidden) {
            return;
        }

        lightbox.hidden = true;
        lightbox.setAttribute('aria-hidden', 'true');
        lightboxImage.src = '';
        lightboxImage.alt = '';
        document.body.classList.remove('lightbox-open');
        document.body.classList.remove('mobile-app-lightbox-open');
        restoreLightboxPosition();
        activeTrigger?.focus({ preventScroll: true });
        activeTrigger = null;
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            openLightbox(trigger);
        });
    });

    closeButton.addEventListener('click', closeLightbox);

    lightbox.addEventListener('click', (event) => {
        if (event.target === lightbox) {
            closeLightbox();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeLightbox();
        }
    });
}
