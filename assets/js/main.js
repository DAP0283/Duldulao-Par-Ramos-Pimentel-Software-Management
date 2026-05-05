/**
 * Main JavaScript File
 * Barangay e-Services Appointment System
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Application initialized');
    
    // Initialize components
    initializeEventListeners();
    initializeDropdowns();
    initializeSearch();
});

/**
 * Initialize event listeners for buttons and interactive elements
 */
function initializeEventListeners() {
    // Handle filter status dropdowns
    const filterStatus = document.getElementById('filter-status');
    if (filterStatus) {
        filterStatus.addEventListener('change', function() {
            // TODO: Implement filtering logic or redirect
            console.log('Filter status changed to:', this.value);
        });
    }

    // Handle search inputs
    const searchInputs = document.querySelectorAll('[id$="-search"], [id^="search-"]');
    searchInputs.forEach(input => {
        input.addEventListener('input', debounce(function(e) {
            console.log('Search query:', e.target.value);
            // TODO: Implement live search
        }, 300));
    });

    // Handle form submissions with validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Initialize dropdown functionality
 */
function initializeDropdowns() {
    const selects = document.querySelectorAll('select.form-control');
    selects.forEach(select => {
        select.addEventListener('change', function() {
            console.log('Dropdown changed:', this.name, this.value);
        });
    });
}

/**
 * Initialize search functionality
 */
function initializeSearch() {
    const searchElements = document.querySelectorAll('[id*="search"]');
    searchElements.forEach(element => {
        element.addEventListener('input', debounce(function() {
            // Perform search operations here
            console.log('Searching for:', this.value);
        }, 500));
    });
}

/**
 * Form validation
 */
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }

        // Additional validation for email fields
        if (field.type === 'email') {
            if (!isValidEmail(field.value)) {
                field.classList.add('error');
                isValid = false;
            }
        }

        // Validation for password fields
        if (field.type === 'password') {
            if (field.value.length < 6) {
                field.classList.add('error');
                isValid = false;
            }
        }
    });

    return isValid;
}

/**
 * Email validation
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Debounce function for performance optimization
 */
function debounce(func, delay) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

/**
 * Format date to readable format
 */
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

/**
 * Show notification/alert messages
 */
function showNotification(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    // Insert at top of main content
    const mainContent = document.querySelector('.dashboard-content') || document.body;
    mainContent.insertBefore(alertDiv, mainContent.firstChild);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

/**
 * Handle logout confirmation
 */
function confirmLogout() {
    return confirm('Are you sure you want to logout?');
}

/**
 * Toggle sidebar on mobile
 */
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
}

/**
 * Export data to CSV (for admin/staff)
 */
function exportToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const csvRow = [];
        cols.forEach(col => {
            csvRow.push('"' + col.innerText + '"');
        });
        csv.push(csvRow.join(','));
    });

    downloadCSV(csv.join('\n'), filename);
}

/**
 * Download CSV file
 */
function downloadCSV(csv, filename) {
    const csvFile = new Blob([csv], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.href = URL.createObjectURL(csvFile);
    downloadLink.download = filename;
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

/**
 * Print document
 */
function printDocument() {
    window.print();
}

/**
 * Add CSS error class to form fields
 */
function addFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.classList.add('error');
        const errorMsg = document.createElement('small');
        errorMsg.style.color = 'var(--danger-color)';
        errorMsg.textContent = message;
        field.parentNode.appendChild(errorMsg);
    }
}

/**
 * Clear field errors
 */
function clearFieldErrors() {
    const errorFields = document.querySelectorAll('.error');
    errorFields.forEach(field => {
        field.classList.remove('error');
        const errorMsg = field.parentNode.querySelector('small');
        if (errorMsg) {
            errorMsg.remove();
        }
    });
}

// Export functions for use in other scripts
window.validateForm = validateForm;
window.showNotification = showNotification;
window.confirmLogout = confirmLogout;
window.toggleSidebar = toggleSidebar;
window.exportToCSV = exportToCSV;
window.printDocument = printDocument;
window.isValidEmail = isValidEmail;
