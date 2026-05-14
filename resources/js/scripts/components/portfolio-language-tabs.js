export function initializePortfolioLanguageTabs() {
    document.querySelectorAll('[data-language-tabs]').forEach((container) => {
        const tabs = container.querySelectorAll('[data-language-tab]');
        const panels = container.querySelectorAll('[data-language-panel]');

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const language = tab.dataset.languageTab;

                tabs.forEach((item) => {
                    const isActive = item === tab;

                    item.classList.toggle('admin-form-tabs__button--active', isActive);
                    item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                panels.forEach((panel) => {
                    const isActive = panel.dataset.languagePanel === language;

                    panel.hidden = !isActive;
                    panel.classList.toggle('admin-form-tabs__panel--hidden', !isActive);
                });
            });
        });
    });
}
