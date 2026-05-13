export function initializeDeleteConfirmationModal() {
    const modal = document.querySelector('[data-delete-confirmation-modal]');
    const deleteButtons = document.querySelectorAll('[data-delete-confirmation-trigger]');

    if (!modal || deleteButtons.length === 0) {
        return;
    }

    const submitButton = modal.querySelector('[data-delete-confirmation-submit]');
    const title = modal.querySelector('[data-delete-confirmation-title]');
    const message = modal.querySelector('[data-delete-confirmation-message]');
    const cancelButtons = modal.querySelectorAll('[data-delete-confirmation-cancel]');
    const defaultTitle = title?.textContent ?? '';
    const defaultMessage = message?.textContent ?? '';
    const defaultSubmitLabel = submitButton?.textContent ?? '';
    let selectedDeleteForm = null;

    deleteButtons.forEach((deleteButton) => {
        deleteButton.addEventListener('click', () => {
            const deleteFormId = deleteButton.dataset.deleteFormId;
            selectedDeleteForm = document.getElementById(deleteFormId);
            title.textContent = deleteButton.dataset.deleteConfirmationTitle || defaultTitle;
            message.textContent = deleteButton.dataset.deleteConfirmationMessage || defaultMessage;
            submitButton.textContent = deleteButton.dataset.deleteConfirmationSubmitLabel || defaultSubmitLabel;
            openDeleteConfirmationModal(modal, submitButton);
        });
    });

    cancelButtons.forEach((cancelButton) => {
        cancelButton.addEventListener('click', () => {
            selectedDeleteForm = null;
            resetDeleteConfirmationModalContent(title, message, submitButton, defaultTitle, defaultMessage, defaultSubmitLabel);
            closeDeleteConfirmationModal(modal);
        });
    });

    submitButton?.addEventListener('click', () => {
        if (selectedDeleteForm) {
            selectedDeleteForm.submit();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isDeleteConfirmationModalOpen(modal)) {
            selectedDeleteForm = null;
            resetDeleteConfirmationModalContent(title, message, submitButton, defaultTitle, defaultMessage, defaultSubmitLabel);
            closeDeleteConfirmationModal(modal);
        }
    });
}

function openDeleteConfirmationModal(modal, submitButton) {
    modal.hidden = false;
    modal.classList.add('admin-modal--is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('admin-modal-open');
    submitButton?.focus();
}

function closeDeleteConfirmationModal(modal) {
    modal.classList.remove('admin-modal--is-open');
    modal.setAttribute('aria-hidden', 'true');
    modal.hidden = true;
    document.body.classList.remove('admin-modal-open');
}

function isDeleteConfirmationModalOpen(modal) {
    return modal.classList.contains('admin-modal--is-open');
}

function resetDeleteConfirmationModalContent(title, message, submitButton, defaultTitle, defaultMessage, defaultSubmitLabel) {
    title.textContent = defaultTitle;
    message.textContent = defaultMessage;
    submitButton.textContent = defaultSubmitLabel;
}
