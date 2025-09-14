// assets/js/script.js
// E-Surat PTUN Banjarmasin - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initSidebar();
    initModals();
    initAlerts();
    initForms();
    initTables();
    initTooltips();
    updateDateTime();
    
    // Update time every second
    setInterval(updateDateTime, 1000);
});

// Sidebar functionality
function initSidebar() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Store sidebar state
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }
    
    // Restore sidebar state
    const sidebarState = localStorage.getItem('sidebarCollapsed');
    if (sidebarState === 'true') {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
    }
    
    // Handle mobile sidebar
    if (window.innerWidth <= 768) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
    }
    
    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            }
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            const sidebarState = localStorage.getItem('sidebarCollapsed');
            if (sidebarState !== 'true') {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
            }
        } else {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }
    });
}

// Modal functionality
function initModals() {
    // Modal open
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-modal-target]')) {
            const modalId = e.target.getAttribute('data-modal-target');
            const modal = document.querySelector(modalId);
            if (modal) {
                showModal(modal);
            }
        }
    });
    
    // Modal close
    document.addEventListener('click', function(e) {
        if (e.target.matches('.modal-close') || e.target.matches('.modal')) {
            const modal = e.target.closest('.modal');
            if (modal && (e.target.matches('.modal-close') || e.target === modal)) {
                hideModal(modal);
            }
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                hideModal(openModal);
            }
        }
    });
}

function showModal(modal) {
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // Focus trap
    const focusableElements = modal.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    if (focusableElements.length) {
        focusableElements[0].focus();
    }
}

function hideModal(modal) {
    modal.classList.remove('show');
    document.body.style.overflow = '';
}

// Alert functionality
function initAlerts() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert[data-auto-hide="true"]');
    alerts.forEach(alert => {
        setTimeout(() => {
            hideAlert(alert);
        }, 5000);
    });
    
    // Manual close alerts
    document.addEventListener('click', function(e) {
        if (e.target.matches('.alert-close')) {
            const alert = e.target.closest('.alert');
            if (alert) {
                hideAlert(alert);
            }
        }
    });
}

function hideAlert(alert) {
    alert.style.animation = 'slideOutUp 0.3s ease forwards';
    setTimeout(() => {
        alert.remove();
    }, 300);
}

function showAlert(message, type = 'info', autoHide = true) {
    const alertContainer = document.querySelector('.alert-container') || createAlertContainer();
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    if (autoHide) alert.setAttribute('data-auto-hide', 'true');
    
    alert.innerHTML = `
        <div class="alert-content">
            <span class="alert-icon">
                ${getAlertIcon(type)}
            </span>
            <span class="alert-message">${message}</span>
            <button type="button" class="alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    alertContainer.appendChild(alert);
    
    if (autoHide) {
        setTimeout(() => {
            hideAlert(alert);
        }, 5000);
    }
}

function createAlertContainer() {
    const container = document.createElement('div');
    container.className = 'alert-container';
    container.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 3000;
        max-width: 400px;
    `;
    document.body.appendChild(container);
    return container;
}

function getAlertIcon(type) {
    const icons = {
        success: '<i class="fas fa-check-circle"></i>',
        error: '<i class="fas fa-exclamation-circle"></i>',
        warning: '<i class="fas fa-exclamation-triangle"></i>',
        info: '<i class="fas fa-info-circle"></i>'
    };
    return icons[type] || icons.info;
}

// Form functionality
function initForms() {
    // Form validation
    const forms = document.querySelectorAll('form[data-validate="true"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-save functionality
    const autoSaveForms = document.querySelectorAll('form[data-auto-save="true"]');
    autoSaveForms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', debounce(() => {
                saveFormData(form);
            }, 1000));
        });
        
        // Restore form data
        restoreFormData(form);
    });
    
    // File upload preview
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview="true"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            previewFile(e.target);
        });
    });
    
    // Dynamic form fields
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-add-field]')) {
            const template = e.target.getAttribute('data-add-field');
            addDynamicField(template);
        }
        
        if (e.target.matches('[data-remove-field]')) {
            removeDynamicField(e.target);
        }
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'Field ini wajib diisi');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });
    
    // Email validation
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            showFieldError(field, 'Format email tidak valid');
            isValid = false;
        }
    });
    
    // Phone validation
    const phoneFields = form.querySelectorAll('input[data-type="phone"]');
    phoneFields.forEach(field => {
        if (field.value && !isValidPhone(field.value)) {
            showFieldError(field, 'Format nomor telepon tidak valid');
            isValid = false;
        }
    });
    
    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    
    field.classList.add('is-invalid');
    
    const error = document.createElement('div');
    error.className = 'invalid-feedback';
    error.textContent = message;
    
    field.parentNode.appendChild(error);
}

function clearFieldError(field) {
    field.classList.remove('is-invalid');
    
    const error = field.parentNode.querySelector('.invalid-feedback');
    if (error) {
        error.remove();
    }
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function isValidPhone(phone) {
    const re = /^[\+]?[0-9\s\-\(\)]{10,}$/;
    return re.test(phone);
}

function saveFormData(form) {
    const formData = new FormData(form);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    localStorage.setItem(`form_${form.id}`, JSON.stringify(data));
}

function restoreFormData(form) {
    const savedData = localStorage.getItem(`form_${form.id}`);
    
    if (savedData) {
        const data = JSON.parse(savedData);
        
        for (let [key, value] of Object.entries(data)) {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) {
                field.value = value;
            }
        }
    }
}

function previewFile(input) {
    const file = input.files[0];
    if (!file) return;
    
    const previewContainer = input.parentNode.querySelector('.file-preview') || createFilePreview(input);
    
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewContainer.innerHTML = `
                <img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 0.5rem;">
                <div class="file-info">
                    <strong>${file.name}</strong><br>
                    <small>${formatFileSize(file.size)}</small>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    } else {
        previewContainer.innerHTML = `
            <div class="file-icon">
                <i class="fas fa-file"></i>
            </div>
            <div class="file-info">
                <strong>${file.name}</strong><br>
                <small>${formatFileSize(file.size)}</small>
            </div>
        `;
    }
}

function createFilePreview(input) {
    const preview = document.createElement('div');
    preview.className = 'file-preview';
    preview.style.cssText = `
        margin-top: 1rem;
        padding: 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    `;
    input.parentNode.appendChild(preview);
    return preview;
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Table functionality
function initTables() {
    // Sortable tables
    const sortableTables = document.querySelectorAll('table[data-sortable="true"]');
    sortableTables.forEach(table => {
        const headers = table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                sortTable(table, header);
            });
        });
    });
    
    // Searchable tables
    const searchInputs = document.querySelectorAll('input[data-table-search]');
    searchInputs.forEach(input => {
        input.addEventListener('input', debounce(() => {
            const tableId = input.getAttribute('data-table-search');
            const table = document.querySelector(tableId);
            if (table) {
                searchTable(table, input.value);
            }
        }, 300));
    });
    
    // Row selection
    document.addEventListener('change', function(e) {
        if (e.target.matches('input[type="checkbox"][data-select-row]')) {
            handleRowSelection(e.target);
        }
        
        if (e.target.matches('input[type="checkbox"][data-select-all]')) {
            handleSelectAll(e.target);
        }
    });
}

function sortTable(table, header) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const columnIndex = Array.from(header.parentNode.children).indexOf(header);
    const sortType = header.getAttribute('data-sort');
    const currentOrder = header.getAttribute('data-order') || 'asc';
    const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
    
    // Clear other headers
    table.querySelectorAll('th[data-sort]').forEach(th => {
        th.removeAttribute('data-order');
        th.classList.remove('sort-asc', 'sort-desc');
    });
    
    // Set current header
    header.setAttribute('data-order', newOrder);
    header.classList.add(`sort-${newOrder}`);
    
    // Sort rows
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();
        
        let comparison = 0;
        
        if (sortType === 'number') {
            comparison = parseFloat(aValue) - parseFloat(bValue);
        } else if (sortType === 'date') {
            comparison = new Date(aValue) - new Date(bValue);
        } else {
            comparison = aValue.localeCompare(bValue);
        }
        
        return newOrder === 'desc' ? -comparison : comparison;
    });
    
    // Reorder table
    rows.forEach(row => tbody.appendChild(row));
}

function searchTable(table, query) {
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const match = text.includes(query.toLowerCase());
        row.style.display = match ? '' : 'none';
    });
}

function handleRowSelection(checkbox) {
    const row = checkbox.closest('tr');
    if (checkbox.checked) {
        row.classList.add('selected');
    } else {
        row.classList.remove('selected');
    }
    
    updateBulkActions();
}

function handleSelectAll(checkbox) {
    const table = checkbox.closest('table');
    const rowCheckboxes = table.querySelectorAll('input[type="checkbox"][data-select-row]');
    
    rowCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        handleRowSelection(cb);
    });
}

function updateBulkActions() {
    const selectedRows = document.querySelectorAll('tr.selected');
    const bulkActions = document.querySelector('.bulk-actions');
    
    if (bulkActions) {
        if (selectedRows.length > 0) {
            bulkActions.style.display = 'block';
            bulkActions.querySelector('.selected-count').textContent = selectedRows.length;
        } else {
            bulkActions.style.display = 'none';
        }
    }
}

// Tooltip functionality
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const element = e.target;
    const text = element.getAttribute('data-tooltip');
    
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.8rem;
        white-space: nowrap;
        z-index: 4000;
        pointer-events: none;
    `;
    
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    tooltip.style.top = `${rect.top - tooltip.offsetHeight - 5}px`;
    tooltip.style.left = `${rect.left + (rect.width - tooltip.offsetWidth) / 2}px`;
    
    element._tooltip = tooltip;
}

function hideTooltip(e) {
    const element = e.target;
    if (element._tooltip) {
        element._tooltip.remove();
        delete element._tooltip;
    }
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function updateDateTime() {
    const now = new Date();
    const options = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZone: 'Asia/Makassar'
    };
    
    const dateTimeString = now.toLocaleDateString('id-ID', options);
    const dateTimeElements = document.querySelectorAll('.current-datetime');
    
    dateTimeElements.forEach(element => {
        element.textContent = dateTimeString;
    });
}

// AJAX helpers
function sendAjaxRequest(url, method = 'GET', data = null, callback = null) {
    const xhr = new XMLHttpRequest();
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (callback) callback(null, response);
                } catch (e) {
                    if (callback) callback(null, xhr.responseText);
                }
            } else {
                if (callback) callback(new Error(`Request failed: ${xhr.status}`), null);
            }
        }
    };
    
    xhr.open(method, url, true);
    
    if (method === 'POST' && data instanceof FormData) {
        xhr.send(data);
    } else if (method === 'POST') {
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send(data);
    } else {
        xhr.send();
    }
}

// Export functions for global use
window.AppUtils = {
    showAlert,
    hideAlert,
    showModal,
    hideModal,
    validateForm,
    sendAjaxRequest,
    debounce,
    formatFileSize
};