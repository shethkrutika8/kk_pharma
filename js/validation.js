/**
 * Client-side Validation Functions for KK Pharma
 * Provides real-time validation for form inputs
 */

// Validation utility functions
const Validation = {
    /**
     * Validate email address
     */
    email: function(value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!value) return { valid: false, message: 'Email is required' };
        if (!emailRegex.test(value)) return { valid: false, message: 'Invalid email format' };
        if (value.length > 100) return { valid: false, message: 'Email is too long (max 100 characters)' };
        return { valid: true, message: '' };
    },

    /**
     * Validate password strength
     */
    password: function(value, confirmPassword = null) {
        if (!value) return { valid: false, message: 'Password is required' };
        if (value.length < 8) return { valid: false, message: 'Password must be at least 8 characters long' };
        if (value.length > 128) return { valid: false, message: 'Password is too long (max 128 characters)' };
        if (!/[A-Z]/.test(value)) return { valid: false, message: 'Password must contain at least one uppercase letter' };
        if (!/[a-z]/.test(value)) return { valid: false, message: 'Password must contain at least one lowercase letter' };
        if (!/[0-9]/.test(value)) return { valid: false, message: 'Password must contain at least one number' };
        if (!/[!@#$%^&*(),.?":{}|<>]/.test(value)) return { valid: false, message: 'Password must contain at least one special character' };
        if (confirmPassword !== null && value !== confirmPassword) return { valid: false, message: 'Passwords do not match' };
        return { valid: true, message: '' };
    },

    /**
     * Validate name
     */
    name: function(value, fieldName = 'Name') {
        if (!value) return { valid: false, message: `${fieldName} is required` };
        if (value.length < 2) return { valid: false, message: `${fieldName} must be at least 2 characters long` };
        if (value.length > 50) return { valid: false, message: `${fieldName} is too long (max 50 characters)` };
        if (!/^[a-zA-Z\s\-']+$/.test(value)) return { valid: false, message: `${fieldName} can only contain letters, spaces, hyphens, and apostrophes` };
        return { valid: true, message: '' };
    },

    /**
     * Validate phone number
     */
    phone: function(value) {
        if (!value) return { valid: false, message: 'Phone number is required' };
        const phoneNumeric = value.replace(/[^0-9]/g, '');
        if (phoneNumeric.length < 10) return { valid: false, message: 'Phone number must be at least 10 digits' };
        if (phoneNumeric.length > 15) return { valid: false, message: 'Phone number is too long (max 15 digits)' };
        if (!/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/.test(value)) {
            return { valid: false, message: 'Invalid phone number format' };
        }
        return { valid: true, message: '' };
    },

    /**
     * Validate text field
     */
    text: function(value, fieldName = 'Text', minLength = 1, maxLength = 255) {
        if (!value && minLength > 0) return { valid: false, message: `${fieldName} is required` };
        if (value.length < minLength) return { valid: false, message: `${fieldName} must be at least ${minLength} characters long` };
        if (value.length > maxLength) return { valid: false, message: `${fieldName} is too long (max ${maxLength} characters)` };
        return { valid: true, message: '' };
    },

    /**
     * Validate number
     */
    number: function(value, fieldName = 'Value', min = null, max = null) {
        if (isNaN(value)) return { valid: false, message: `${fieldName} must be a number` };
        const numValue = parseFloat(value);
        if (min !== null && numValue < min) return { valid: false, message: `${fieldName} must be at least ${min}` };
        if (max !== null && numValue > max) return { valid: false, message: `${fieldName} must be at most ${max}` };
        return { valid: true, message: '' };
    },

    /**
     * Validate URL
     */
    url: function(value) {
        if (!value) return { valid: false, message: 'URL is required' };
        try {
            new URL(value);
            return { valid: true, message: '' };
        } catch {
            return { valid: false, message: 'Invalid URL format' };
        }
    }
};

// Form validation handler
class FormValidator {
    constructor(formId, options = {}) {
        this.form = document.getElementById(formId);
        this.options = {
            validateOnBlur: true,
            validateOnInput: false,
            showErrorMessages: true,
            ...options
        };
        this.errors = {};
        this.init();
    }

    init() {
        if (!this.form) return;

        // Add event listeners
        if (this.options.validateOnBlur) {
            this.form.addEventListener('blur', (e) => this.handleFieldBlur(e), true);
        }

        if (this.options.validateOnInput) {
            this.form.addEventListener('input', (e) => this.handleFieldInput(e));
        }

        // Validate on submit
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }

    handleFieldBlur(e) {
        const field = e.target;
        if (field.name) {
            this.validateField(field);
        }
    }

    handleFieldInput(e) {
        const field = e.target;
        if (field.name) {
            this.validateField(field);
        }
    }

    validateField(field) {
        const value = field.value.trim();
        const fieldType = field.dataset.validate || field.type;
        const fieldName = field.dataset.fieldName || field.name;
        let result = { valid: true, message: '' };

        switch (fieldType) {
            case 'email':
                result = Validation.email(value);
                break;
            case 'password':
                const confirmPassword = this.form.querySelector('[name="confirm_password"]')?.value;
                result = Validation.password(value, confirmPassword);
                break;
            case 'confirm_password':
                const password = this.form.querySelector('[name="password"]')?.value;
                result = Validation.password(password, value);
                break;
            case 'tel':
            case 'phone':
                result = Validation.phone(value);
                break;
            case 'number':
                const min = parseFloat(field.dataset.min);
                const max = parseFloat(field.dataset.max);
                result = Validation.number(value, fieldName, min, max);
                break;
            case 'url':
                result = Validation.url(value);
                break;
            case 'name':
            case 'text':
            default:
                const minLength = parseInt(field.dataset.minLength) || 1;
                const maxLength = parseInt(field.dataset.maxLength) || 255;
                result = Validation.text(value, fieldName, minLength, maxLength);
                break;
        }

        this.updateFieldValidation(field, result);
        return result;
    }

    updateFieldValidation(field, result) {
        const feedbackElement = field.parentElement.querySelector('.invalid-feedback') ||
                              field.parentElement.querySelector('.error-message');

        if (result.valid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            if (feedbackElement) {
                feedbackElement.textContent = '';
                feedbackElement.style.display = 'none';
            }
            delete this.errors[field.name];
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            if (feedbackElement && this.options.showErrorMessages) {
                feedbackElement.textContent = result.message;
                feedbackElement.style.display = 'block';
            }
            this.errors[field.name] = result.message;
        }
    }

    validateForm() {
        const fields = this.form.querySelectorAll('[name]');
        let isValid = true;

        fields.forEach(field => {
            const result = this.validateField(field);
            if (!result.valid) {
                isValid = false;
            }
        });

        return isValid;
    }

    handleSubmit(e) {
        if (!this.validateForm()) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        return true;
    }

    getErrors() {
        return this.errors;
    }

    hasErrors() {
        return Object.keys(this.errors).length > 0;
    }
}

// Password strength indicator
class PasswordStrengthIndicator {
    constructor(passwordField, strengthBar) {
        this.passwordField = passwordField;
        this.strengthBar = strengthBar;
        this.init();
    }

    init() {
        this.passwordField.addEventListener('input', () => this.updateStrength());
    }

    updateStrength() {
        const password = this.passwordField.value;
        let strength = 0;
        let feedback = '';

        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;

        const maxStrength = 6;
        const percentage = (strength / maxStrength) * 100;

        if (strength <= 2) {
            feedback = 'Weak';
            this.strengthBar.className = 'progress-bar bg-danger';
        } else if (strength <= 4) {
            feedback = 'Medium';
            this.strengthBar.className = 'progress-bar bg-warning';
        } else {
            feedback = 'Strong';
            this.strengthBar.className = 'progress-bar bg-success';
        }

        this.strengthBar.style.width = percentage + '%';
        this.strengthBar.textContent = feedback;
    }
}

// Initialize form validators when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize login form validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        new FormValidator('loginForm', {
            validateOnBlur: true,
            validateOnInput: false
        });
    }

    // Initialize registration form validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        new FormValidator('registerForm', {
            validateOnBlur: true,
            validateOnInput: false
        });

        // Initialize password strength indicator if present
        const passwordField = document.getElementById('password');
        const strengthBar = document.getElementById('passwordStrength');
        if (passwordField && strengthBar) {
            new PasswordStrengthIndicator(passwordField, strengthBar);
        }
    }

    // Initialize contact form validation
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        new FormValidator('contactForm', {
            validateOnBlur: true,
            validateOnInput: false
        });
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { Validation, FormValidator, PasswordStrengthIndicator };
}
