export function initializeMediaFileInputs() {
    const fileInputs = document.querySelectorAll('[data-media-file-input]');

    fileInputs.forEach((fileInput) => {
        fileInput.addEventListener('change', () => {
            const filenameTarget = fileInput
                .closest('.admin-media-upload__picker-row')
                ?.querySelector('[data-media-file-name]');

            if (!filenameTarget) {
                return;
            }

            filenameTarget.textContent = fileInput.files?.[0]?.name || 'Failas nepasirinktas';
        });
    });
}
