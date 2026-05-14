import { initializeDeleteConfirmationModal } from './delete-confirmation-modal.js';
import { initializeLocalTimestamps } from './local-timestamps.js';
import { initializePasswordToggle } from './password-toggle.js';
import { initializeProjectDetails } from './project-details.js';

// Reusable component scripts should be imported here.
export function initializeComponentScripts() {
    initializeDeleteConfirmationModal();
    initializeLocalTimestamps();
    initializePasswordToggle();
    initializeProjectDetails();
}
