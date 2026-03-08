/**
 * Form Validation and Utilities
 * Barangay e-Services Appointment System
 */

/**
 * Validate form on submit
 */
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', handleFormSubmit);
    });

    // Real-time validation for email fields
    const emailFields = document.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        field.addEventListener('blur', validateEmailField);
        field.addEventListener('input', validateEmailField);
    });

    // Real-time validation for password fields
    const passwordFields = document.querySelectorAll('input[type="password"]');
    passwordFields.forEach(field => {
        field.addEventListener('input', validatePasswordField);
    });

    // Real-time validation for phone fields
    const phoneFields = document.querySelectorAll('input[type="tel"]');
    phoneFields.forEach(field => {
        field.addEventListener('input', validatePhoneField);
    });

    // Real-time validation for date fields
    const dateFields = document.querySelectorAll('input[type="date"]');
    dateFields.forEach(field => {
        field.addEventListener('change', validateDateField);
    });
});

/**
 * Handle form submission
 */
function handleFormSubmit(event) {
    const form = event.target;
    
    // Clear previous errors
    clearAllErrors(form);

    // Validate form
    if (!validateAllFields(form)) {
        event.preventDefault();
        showFormError(form);
        return false;
    }

    return true;
}

/**
 * Validate all form fields
 */
function validateAllFields(form) {
    let isValid = true;
    const fields = form.querySelectorAll('[required]');

    fields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });

    return isValid;
}

/**
 * Validate individual field
 */
function validateField(field) {
    const value = field.value.trim();

    // Check if field is empty
    if (!value) {
        addError(field, 'This field is required');
        return false;
    }

    // Email validation
    if (field.type === 'email') {
        if (!isValidEmail(value)) {
            addError(field, 'Please enter a valid email address');
            return false;
        }
    }

    // Password validation
    if (field.type === 'password') {
        if (value.length < 6) {
            addError(field, 'Password must be at least 6 characters long');
            return false;
        }
    }

    // Phone validation
    if (field.type === 'tel') {
        if (!isValidPhone(value)) {
            addError(field, 'Please enter a valid phone number');
            return false;
        }
    }

    // Date validation
    if (field.type === 'date') {
        if (!isValidDate(value)) {
            addError(field, 'Please enter a valid date');
            return false;
        }
    }

    // Number validation
    if (field.type === 'number') {
        if (isNaN(value) || value === '') {
            addError(field, 'Please enter a valid number');
            return false;
        }
    }

    removeError(field);
    return true;
}

/**
 * Validate email field
 */
function validateEmailField(event) {
    const field = event.target;
    if (field.value.trim() && !isValidEmail(field.value)) {
        addError(field, 'Invalid email format');
    } else {
        removeError(field);
    }
}

/**
 * Validate password field
 */
function validatePasswordField(event) {
    const field = event.target;
    if (field.value.length > 0 && field.value.length < 6) {
        addError(field, 'Minimum 6 characters required');
    } else {
        removeError(field);
    }
}

/**
 * Validate phone field
 */
function validatePhoneField(event) {
    const field = event.target;
    if (field.value.trim() && !isValidPhone(field.value)) {
        addError(field, 'Invalid phone number format');
    } else {
        removeError(field);
    }
}

/**
 * Validate date field
 */
function validateDateField(event) {
    const field = event.target;
    if (field.value && !isValidDate(field.value)) {
        addError(field, 'Invalid date');
    } else {
        removeError(field);
    }
}

/**
 * Check if email is valid
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Check if phone number is valid
 */
function isValidPhone(phone) {
    // Remove all non-digit characters
    const cleaned = phone.replace(/\D/g, '');
    // Check if at least 10 digits
    return cleaned.length >= 10;
}

/**
 * Check if date is valid
 */
function isValidDate(dateString) {
    const date = new Date(dateString);
    return date instanceof Date && !isNaN(date);
}

/**
 * Add error styling to field
 */
function addError(field, message) {
    field.classList.add('form-error');
    
    // Remove existing error message
    const existingError = field.parentNode.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }

    // Add error message
    const errorMsg = document.createElement('small');
    errorMsg.className = 'error-message';
    errorMsg.style.color = '#ef4444';
    errorMsg.style.display = 'block';
    errorMsg.style.marginTop = '0.25rem';
    errorMsg.textContent = message;
    field.parentNode.appendChild(errorMsg);
}

/**
 * Remove error styling from field
 */
function removeError(field) {
    field.classList.remove('form-error');
    const errorMsg = field.parentNode.querySelector('.error-message');
    if (errorMsg) {
        errorMsg.remove();
    }
}

/**
 * Clear all errors in form
 */
function clearAllErrors(form) {
    const errorFields = form.querySelectorAll('.form-error');
    errorFields.forEach(field => {
        removeError(field);
    });
}

/**
 * Show general form error
 */
function showFormError(form) {
    let existingAlert = form.querySelector('.alert-danger');
    if (!existingAlert) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger';
        alertDiv.textContent = 'Please correct the errors above and try again.';
        form.insertBefore(alertDiv, form.firstChild);
    }
}

/**
 * Validate password confirmation match
 */
function validatePasswordMatch(passwordFieldId, confirmPasswordFieldId) {
    const passwordField = document.getElementById(passwordFieldId);
    const confirmPasswordField = document.getElementById(confirmPasswordFieldId);

    if (passwordField && confirmPasswordField) {
        if (passwordField.value !== confirmPasswordField.value) {
            addError(confirmPasswordField, 'Passwords do not match');
            return false;
        } else {
            removeError(confirmPasswordField);
            return true;
        }
    }
    return true;
}

/**
 * Format phone number as user types
 */
function formatPhoneNumber(fieldId) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                value = '(' + value.substring(0, 3) + ') ' + value.substring(3, 6) + '-' + value.substring(6, 10);
            }
            e.target.value = value;
        });
    }
}

/**
 * Set maximum date to today (for date of birth fields)
 */
function setMaxDateToToday(fieldId) {
    const field = document.getElementById(fieldId);
    if (field && field.type === 'date') {
        const today = new Date().toISOString().split('T')[0];
        field.max = today;
    }
}

/**
 * Prevent future dates
 */
function preventFutureDate(fieldId) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate > today) {
                addError(field, 'Date cannot be in the future');
                this.value = '';
            } else {
                removeError(field);
            }
        });
    }
}

// Export validation functions
window.validateField = validateField;
window.validateAllFields = validateAllFields;
window.validatePasswordMatch = validatePasswordMatch;
window.formatPhoneNumber = formatPhoneNumber;
window.setMaxDateToToday = setMaxDateToToday;
window.preventFutureDate = preventFutureDate;
