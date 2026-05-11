export function initializePasswordToggle() {
    const toggleButtons = document.querySelectorAll('[data-password-toggle-trigger]');

    toggleButtons.forEach((toggleButton) => {
        const targetSelector = toggleButton.dataset.passwordToggleTarget;
        const passwordInput = document.querySelector(targetSelector);
        const hiddenPasswordIcon = toggleButton.querySelector('[data-password-hidden-icon]');
        const visiblePasswordIcon = toggleButton.querySelector('[data-password-visible-icon]');

        if (!passwordInput) {
            return;
        }

        updatePasswordToggleState(toggleButton, hiddenPasswordIcon, visiblePasswordIcon, passwordInput.type === 'text');

        toggleButton.addEventListener('click', () => {
            const isPasswordVisible = passwordInput.type === 'text';
            const shouldShowPassword = !isPasswordVisible;

            passwordInput.type = shouldShowPassword ? 'text' : 'password';
            updatePasswordToggleState(toggleButton, hiddenPasswordIcon, visiblePasswordIcon, shouldShowPassword);
        });
    });
}

function updatePasswordToggleState(toggleButton, hiddenPasswordIcon, visiblePasswordIcon, isPasswordVisible) {
    toggleButton.setAttribute('aria-label', isPasswordVisible ? 'Hide password' : 'Show password');
    toggleButton.setAttribute('title', isPasswordVisible ? 'Hide password' : 'Show password');
    toggleButton.setAttribute('aria-pressed', isPasswordVisible ? 'true' : 'false');

    if (hiddenPasswordIcon && visiblePasswordIcon) {
        setIconVisibility(hiddenPasswordIcon, !isPasswordVisible);
        setIconVisibility(visiblePasswordIcon, isPasswordVisible);
    }
}

function setIconVisibility(iconElement, shouldShowIcon) {
    iconElement.toggleAttribute('hidden', !shouldShowIcon);
    iconElement.classList.toggle('admin-password-field__icon--is-hidden', !shouldShowIcon);
}
