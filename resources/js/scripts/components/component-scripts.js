import { initializeCopyMediaUrlButtons } from './copy-media-url.js';
import { initializeDeleteConfirmationModal } from './delete-confirmation-modal.js';
import { initializeLocalTimestamps } from './local-timestamps.js';
import { initializeMediaFileInputs } from './media-file-input.js';
import { initializePasswordToggle } from './password-toggle.js';
import { initializeMigrationAutosave } from './migration-autosave.js';

// Reusable component scripts should be imported here.
export function initializeComponentScripts() {
    initializeCopyMediaUrlButtons();
    initializeDeleteConfirmationModal();
    initializeLocalTimestamps();
    initializeMediaFileInputs();
    initializePasswordToggle();
    initializeMigrationAutosave();

    if (document.querySelector('[data-rich-text-editor]')) {
        import('./rich-text-editor.js').then(({ initializeRichTextEditors }) => {
            initializeRichTextEditors();
        });
    }
}
