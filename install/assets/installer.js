/**
 * Contact Form Installation Wizard JavaScript
 * Interactive functionality for the one-click installer
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Contact Form Installer v1.0 Loaded');

    // Initialize installer functionality
    initializeInstaller();
});

/**
 * Initialize the installer interface
 */
function initializeInstaller() {
    // Add smooth animations
    animateStepTransitions();

    // Initialize form validation
    initializeFormValidation();

    // Add keyboard shortcuts
    addKeyboardShortcuts();

    // Auto-save form data
    enableAutoSave();

    console.log('Installer initialized successfully');
}

/**
 * Animate step transitions
 */
function animateStepTransitions() {
    const stepContent = document.querySelector('.step-content');
    if (stepContent) {
        stepContent.style.opacity = '0';
        stepContent.style.transform = 'translateY(20px)';

        setTimeout(() => {
            stepContent.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            stepContent.style.opacity = '1';
            stepContent.style.transform = 'translateY(0)';
        }, 100);
    }
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', validateField);
            input.addEventListener('input', clearValidationErrors);
        });

        // Form submission validation
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Validate individual field
 */
function validateField(event) {
    const field = event.target;
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');

    // Clear previous validation
    clearFieldValidation(field);

    // Required field validation
    if (required && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }

    // Type-specific validation
    switch (type) {
        case 'email':
            if (value && !isValidEmail(value)) {
                showFieldError(field, 'Please enter a valid email address');
                return false;
            }
            break;

        case 'password':
            if (value && value.length < 8) {
                showFieldError(field, 'Password must be at least 8 characters long');
                return false;
            }
            break;

        case 'url':
            if (value && !isValidUrl(value)) {
                showFieldError(field, 'Please enter a valid URL');
                return false;
            }
            break;
    }

    // Custom validation based on field name
    if (field.name === 'confirm_password') {
        const password = document.querySelector('input[name="password"]');
        if (password && value !== password.value) {
            showFieldError(field, 'Passwords do not match');
            return false;
        }
    }

    if (field.name === 'db_port') {
        const port = parseInt(value);
        if (value && (isNaN(port) || port < 1 || port > 65535)) {
            showFieldError(field, 'Port must be a number between 1 and 65535');
            return false;
        }
    }

    // Show success if field is valid and has value
    if (value) {
        showFieldSuccess(field);
    }

    return true;
}

/**
 * Validate entire form
 */
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!validateField({ target: input })) {
            isValid = false;
        }
    });

    return isValid;
}

/**
 * Clear validation errors on input
 */
function clearValidationErrors(event) {
    const field = event.target;
    clearFieldValidation(field);
}

/**
 * Clear field validation styling
 */
function clearFieldValidation(field) {
    field.classList.remove('error', 'success');

    // Remove error/success messages
    const parent = field.closest('.form-group');
    if (parent) {
        const existingError = parent.querySelector('.form-error');
        const existingSuccess = parent.querySelector('.form-success');

        if (existingError) existingError.remove();
        if (existingSuccess) existingSuccess.remove();
    }
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    field.classList.add('error');
    field.classList.remove('success');

    const parent = field.closest('.form-group');
    if (parent) {
        // Remove existing messages
        const existing = parent.querySelector('.form-error, .form-success');
        if (existing) existing.remove();

        // Add error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        errorDiv.textContent = message;
        parent.appendChild(errorDiv);
    }
}

/**
 * Show field success
 */
function showFieldSuccess(field, message = 'Looks good!') {
    field.classList.add('success');
    field.classList.remove('error');

    const parent = field.closest('.form-group');
    if (parent) {
        // Remove existing messages
        const existing = parent.querySelector('.form-error, .form-success');
        if (existing) existing.remove();

        // Add success message
        const successDiv = document.createElement('div');
        successDiv.className = 'form-success';
        successDiv.textContent = message;
        parent.appendChild(successDiv);
    }
}

/**
 * Email validation
 */
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * URL validation
 */
function isValidUrl(url) {
    try {
        new URL(url);
        return true;
    } catch {
        return false;
    }
}

/**
 * Add keyboard shortcuts
 */
function addKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to proceed to next step
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            const nextBtn = document.querySelector('#next-button-container .btn-primary');
            if (nextBtn && !nextBtn.disabled) {
                nextBtn.click();
            }
        }

        // Alt + Left Arrow to go back
        if (e.altKey && e.key === 'ArrowLeft') {
            const prevBtn = document.querySelector('.btn-secondary');
            if (prevBtn) {
                prevBtn.click();
            }
        }
    });
}

/**
 * Enable auto-save for form data
 */
function enableAutoSave() {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');

        inputs.forEach(input => {
            input.addEventListener('change', function() {
                saveFormData(form);
            });
        });
    });

    // Restore saved data
    restoreFormData();
}

/**
 * Save form data to sessionStorage
 */
function saveFormData(form) {
    const formData = new FormData(form);
    const data = {};

    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }

    const step = window.Installer?.currentStep || 1;
    sessionStorage.setItem(`installer_step_${step}`, JSON.stringify(data));
}

/**
 * Restore form data from sessionStorage
 */
function restoreFormData() {
    const step = window.Installer?.currentStep || 1;
    const savedData = sessionStorage.getItem(`installer_step_${step}`);

    if (savedData) {
        try {
            const data = JSON.parse(savedData);

            Object.keys(data).forEach(key => {
                const field = document.querySelector(`[name="${key}"]`);
                if (field && field.type !== 'password') {
                    field.value = data[key];
                }
            });
        } catch (e) {
            console.warn('Failed to restore form data:', e);
        }
    }
}

/**
 * Database connection testing functionality
 */
window.DatabaseTester = {
    /**
     * Test database connection
     */
    async testConnection(formData) {
        const testContainer = document.querySelector('.connection-test');
        if (!testContainer) return;

        // Show testing state
        testContainer.innerHTML = `
            <div class="test-result testing">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Testing database connection...</span>
            </div>
        `;

        try {
            const response = await fetch('ajax/test-database.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                testContainer.innerHTML = `
                    <div class="test-result success">
                        <i class="fas fa-check-circle"></i>
                        <span>Database connection successful!</span>
                    </div>
                    <div class="test-details">
                        <p><strong>Server:</strong> ${result.details.host}:${result.details.port}</p>
                        <p><strong>Database:</strong> ${result.details.database}</p>
                        <p><strong>Connection Time:</strong> ${result.details.time}ms</p>
                    </div>
                `;

                // Enable next button
                const nextBtn = document.querySelector('#next-button-container .btn-primary');
                if (nextBtn) {
                    nextBtn.disabled = false;
                }

                return true;
            } else {
                testContainer.innerHTML = `
                    <div class="test-result error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Connection failed: ${result.error}</span>
                    </div>
                    <div class="error-details">
                        <p><strong>Error:</strong> ${result.details?.title || 'Unknown error'}</p>
                        <p><strong>Message:</strong> ${result.details?.message || result.error}</p>
                        <p><strong>Action:</strong> ${result.details?.userAction || 'Please check your settings and try again.'}</p>
                    </div>
                `;

                return false;
            }
        } catch (error) {
            testContainer.innerHTML = `
                <div class="test-result error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Connection test failed: ${error.message}</span>
                </div>
            `;

            return false;
        }
    },

    /**
     * Auto-test connection when form changes
     */
    enableAutoTest() {
        const form = document.querySelector('#database-form');
        if (!form) return;

        const inputs = form.querySelectorAll('input[required]');
        let testTimeout;

        inputs.forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(testTimeout);

                // Test after 1 second of inactivity
                testTimeout = setTimeout(() => {
                    if (validateForm(form)) {
                        const formData = new FormData(form);
                        const data = {};
                        for (let [key, value] of formData.entries()) {
                            data[key] = value;
                        }

                        DatabaseTester.testConnection(data);
                    }
                }, 1000);
            });
        });
    }
};

/**
 * Utility functions
 */
window.InstallerUtils = {
    /**
     * Show confirmation dialog
     */
    confirm(message, callback) {
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = `
            <div class="loading-content">
                <h3 style="margin-bottom: 16px;">Confirm Action</h3>
                <p style="margin-bottom: 24px;">${message}</p>
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button class="btn btn-secondary" onclick="this.closest('.loading-overlay').remove()">
                        Cancel
                    </button>
                    <button class="btn btn-primary" onclick="this.closest('.loading-overlay').remove(); (${callback})();">
                        Confirm
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
    },

    /**
     * Copy text to clipboard
     */
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            window.Installer.showSuccess('Copied to clipboard!');
        } catch (err) {
            console.error('Failed to copy text:', err);
            window.Installer.showError('Failed to copy to clipboard');
        }
    },

    /**
     * Download file
     */
    downloadFile(content, filename, mimeType = 'text/plain') {
        const blob = new Blob([content], { type: mimeType });
        const url = URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        URL.revokeObjectURL(url);
    }
};

// Export for global use
window.InstallerJS = {
    validateField,
    validateForm,
    showFieldError,
    showFieldSuccess,
    clearFieldValidation,
    DatabaseTester,
    InstallerUtils
};
