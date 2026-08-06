export function initializeCopyMediaUrlButtons() {
    const copyButtons = document.querySelectorAll('[data-copy-url]');

    copyButtons.forEach((button) => {
        button.addEventListener('click', async () => {
            const url = button.dataset.copyUrl;

            if (!url) {
                return;
            }

            await copyText(url);
            showCopiedState(button);
        });
    });
}

async function copyText(text) {
    if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(text);
        return;
    }

    const input = document.createElement('textarea');
    input.value = text;
    input.setAttribute('readonly', '');
    input.style.position = 'fixed';
    input.style.opacity = '0';
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    input.remove();
}

function showCopiedState(button) {
    const defaultLabel = button.dataset.copyDefaultLabel || button.textContent;
    const successLabel = button.dataset.copySuccessLabel || defaultLabel;

    button.textContent = successLabel;

    window.setTimeout(() => {
        button.textContent = defaultLabel;
    }, 1600);
}
