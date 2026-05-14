export function initializeProjectDetails() {
    const fields = document.querySelectorAll('[data-project-details]');

    fields.forEach((field) => {
        const input = field.querySelector('[data-project-detail-input]');
        const addButton = field.querySelector('[data-project-detail-add]');
        const list = field.querySelector('[data-project-detail-list]');
        const inputName = field.dataset.projectDetailName || 'project_details[]';

        if (!input || !addButton || !list) {
            return;
        }

        addButton.addEventListener('click', () => {
            addProjectDetail(input, list, inputName);
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                addProjectDetail(input, list, inputName);
            }
        });

        list.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-project-detail-remove]');

            if (removeButton) {
                removeButton.closest('[data-project-detail-item]')?.remove();
            }
        });
    });
}

function addProjectDetail(input, list, inputName) {
    const value = input.value.trim();

    if (!value) {
        return;
    }

    list.appendChild(createProjectDetailItem(value, inputName));
    input.value = '';
    input.focus();
}

function createProjectDetailItem(value, inputName) {
    const item = document.createElement('div');
    item.className = 'admin-form__project-detail-item';
    item.dataset.projectDetailItem = '';

    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = inputName;
    hiddenInput.value = value;

    const label = document.createElement('span');
    label.textContent = value;

    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.className = 'admin-form__project-detail-remove';
    removeButton.title = 'Remove detail';
    removeButton.setAttribute('aria-label', `Remove ${value}`);
    removeButton.dataset.projectDetailRemove = '';
    removeButton.innerHTML = `
        <svg viewBox="0 0 20 20" focusable="false" aria-hidden="true">
            <path fill="currentColor" fill-rule="evenodd" d="M5.29 5.29a1 1 0 0 1 1.42 0L10 8.59l3.29-3.3a1 1 0 1 1 1.42 1.42L11.41 10l3.3 3.29a1 1 0 0 1-1.42 1.42L10 11.41l-3.29 3.3a1 1 0 0 1-1.42-1.42L8.59 10l-3.3-3.29a1 1 0 0 1 0-1.42Z" clip-rule="evenodd" />
        </svg>
    `;

    item.append(hiddenInput, label, removeButton);

    return item;
}
