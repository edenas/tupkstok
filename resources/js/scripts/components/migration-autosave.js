export function initializeMigrationAutosave() {
    document.querySelectorAll('[data-migration-autosave]').forEach((form) => {
        const notice = form.querySelector('[data-save-notice]');
        const next = form.querySelector('[data-continue]');
        let timer;
        let saving = false;
        const save = async () => {
            saving = true;
            next?.setAttribute('aria-disabled', 'true');
            notice.textContent = 'Išsaugoma…';
            try {
                const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error('Save failed');
                notice.textContent = `✓ ${form.dataset.successMessage}`;
                form.dataset.saved = '1';
            } catch {
                notice.textContent = 'Nepavyko išsaugoti nustatymų. Bandykite dar kartą.';
            } finally {
                saving = false;
                next?.removeAttribute('aria-disabled');
            }
        };
        form.addEventListener('change', () => { clearTimeout(timer); timer = setTimeout(save, 300); });
        form.addEventListener('submit', (event) => event.preventDefault());
        next?.addEventListener('click', (event) => { if (saving || form.dataset.saved !== '1') event.preventDefault(); });
        if (form.dataset.saved !== '1') save();
    });
}
