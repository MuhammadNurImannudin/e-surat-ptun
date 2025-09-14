</div>
        </main>
    </div>

    <!-- Custom JavaScript -->
    <script src="assets/js/script.js"></script>
    
    <!-- Additional Scripts -->
    <script>
        // Page-specific initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            initTooltips();
            
            // Initialize dropdown menus
            initDropdowns();
            
            // Auto-hide alerts
            autoHideAlerts();
            
            // Initialize page-specific features
            initPageFeatures();
            
            // Show page loaded notification
            setTimeout(() => {
                showNotification('Halaman berhasil dimuat', 'success', 3000);
            }, 500);
        });

        // Dropdown functionality
        function initDropdowns() {
            document.addEventListener('click', function(e) {
                // Toggle dropdown
                if (e.target.matches('.dropdown-toggle') || e.target.closest('.dropdown-toggle')) {
                    e.preventDefault();
                    const dropdown = e.target.closest('.dropdown');
                    const menu = dropdown.querySelector('.dropdown-menu');
                    
                    // Close other dropdowns
                    document.querySelectorAll('.dropdown-menu.show').forEach(otherMenu => {
                        if (otherMenu !== menu) {
                            otherMenu.classList.remove('show');
                        }
                    });
                    
                    // Toggle current dropdown
                    menu.classList.toggle('show');
                }
                
                // Close dropdown when clicking outside
                if (!e.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            });
        }

        // Enhanced notification system
        function showNotification(message, type = 'info', duration = 5000, title = null) {
            const container = document.getElementById('notificationContainer');
            const notification = document.createElement('div');
            
            notification.className = `notification ${type}`;
            
            const icons = {
                success: 'fa-check',
                error: 'fa-times',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info'
            };
            
            const titles = {
                success: title || 'Berhasil',
                error: title || 'Error',
                warning: title || 'Peringatan',
                info: title || 'Informasi'
            };
            
            notification.innerHTML = `
                <div class="notification-content">
                    <div class="notification-icon">
                        <i class="fas ${icons[type]}"></i>
                    </div>
                    <div class="notification-text">
                        <div class="notification-title">${titles[type]}</div>
                        <div class="notification-message">${message}</div>
                    </div>
                    <button type="button" class="notification-close" onclick="removeNotification(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            container.appendChild(notification);
            
            // Show notification
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Auto remove
            if (duration > 0) {
                setTimeout(() => {
                    removeNotification(notification.querySelector('.notification-close'));
                }, duration);
            }
            
            return notification;
        }

        function removeNotification(button) {
            const notification = button.closest('.notification');
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }

        // Auto-hide alerts
        function autoHideAlerts() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.style.animation = 'slideOutUp 0.3s ease forwards';
                        setTimeout(() => {
                            alert.remove();
                        }, 300);
                    }
                }, 5000);
            });
        }

        // Loading overlay functions
        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('show');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('show');
        }

        // Refresh dashboard
        function refreshDashboard() {
            showLoading();
            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        // Print functionality
        function printPage() {
            window.print();
        }

        // Export functionality
        function exportData(format, type = 'all') {
            showLoading();
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'pages/export/export.php';
            
            const formatInput = document.createElement('input');
            formatInput.type = 'hidden';
            formatInput.name = 'format';
            formatInput.value = format;
            
            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'type';
            typeInput.value = type;
            
            form.appendChild(formatInput);
            form.appendChild(typeInput);
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
            
            setTimeout(() => {
                hideLoading();
                showNotification(`Data berhasil diekspor dalam format ${format.toUpperCase()}`, 'success');
            }, 2000);
        }

        // Page-specific feature initialization
        function initPageFeatures() {
            const page = document.body.className.split('-')[0];
            
            switch (page) {
                case 'index':
                    initDashboardFeatures();
                    break;
                case 'surat':
                    initSuratFeatures();
                    break;
                case 'report':
                    initReportFeatures();
                    break;
            }
        }

        function initDashboardFeatures() {
            // Dashboard-specific features
            console.log('Dashboard features initialized');
        }

        function initSuratFeatures() {
            // Surat management features
            console.log('Surat features initialized');
        }

        function initReportFeatures() {
            // Report features
            console.log('Report features initialized');
        }

        // Enhanced form validation
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
            
            return isValid;
        }

        function showFieldError(field, message) {
            clearFieldError(field);
            
            field.classList.add('is-invalid');
            field.style.borderColor = 'var(--danger-color)';
            
            const error = document.createElement('div');
            error.className = 'invalid-feedback';
            error.textContent = message;
            error.style.cssText = `
                color: var(--danger-color);
                font-size: 0.8rem;
                margin-top: 0.25rem;
                display: block;
            `;
            
            field.parentNode.appendChild(error);
        }

        function clearFieldError(field) {
            field.classList.remove('is-invalid');
            field.style.borderColor = '';
            
            const error = field.parentNode.querySelector('.invalid-feedback');
            if (error) {
                error.remove();
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + S untuk save (jika ada form)
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                const form = document.querySelector('form');
                if (form) {
                    form.dispatchEvent(new Event('submit'));
                }
            }
            
            // Escape untuk close modal
            if (e.key === 'Escape') {
                const modal = document.querySelector('.modal.show');
                if (modal) {
                    hideModal(modal);
                }
            }
            
            // Ctrl + N untuk tambah baru
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                const addButton = document.querySelector('a[href*="tambah"]');
                if (addButton) {
                    addButton.click();
                }
            }
        });

        // Service Worker for offline functionality
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('sw.js')
                    .then(function(registration) {
                        console.log('SW registered: ', registration);
                    })
                    .catch(function(registrationError) {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }

        // Error handling
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.error);
            showNotification('Terjadi kesalahan pada sistem. Silakan refresh halaman.', 'error', 10000);
        });

        // Connection status
        window.addEventListener('online', function() {
            showNotification('Koneksi internet tersedia', 'success', 3000);
        });

        window.addEventListener('offline', function() {
            showNotification('Tidak ada koneksi internet', 'warning', 0);
        });
    </script>

    <!-- Custom CSS for dropdown and additional styles -->
    <style>
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-toggle {
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: background-color 0.3s ease;
        }

        .dropdown-toggle:hover {
            background: var(--light-color);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 0.5rem;
            box-shadow: var(--shadow-lg);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--dark-color);
            text-decoration: none;
            transition: background-color 0.3s ease;
            border: none;
            width: 100%;
            text-align: left;
            background: none;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background: var(--light-color);
            color: var(--primary-color);
        }

        .dropdown-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 0.5rem 0;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-header-content h1 {
            margin: 0 0 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-header-content p {
            margin: 0;
            color: #6b7280;
        }

        .page-header-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .alert-dismissible {
            position: relative;
        }

        .alert-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-close {
            background: none;
            border: none;
            color: inherit;
            opacity: 0.7;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 0.25rem;
            margin-left: auto;
            transition: opacity 0.3s ease;
        }

        .alert-close:hover {
            opacity: 1;
        }

        .header-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .current-time {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #6b7280;
        }

        .user-details {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--dark-color);
        }

        .user-role {
            font-size: 0.8rem;
            color: #6b7280;
        }

        @keyframes slideOutUp {
            to {
                transform: translateY(-100%);
                opacity: 0;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: stretch;
            }

            .page-header-actions {
                justify-content: stretch;
            }

            .page-header-actions .btn {
                flex: 1;
                justify-content: center;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .header-right {
                justify-content: space-between;
            }

            .current-time {
                font-size: 0.8rem;
            }
        }
    </style>

    <!-- Footer -->
    <footer class="app-footer" style="background: white; border-top: 1px solid #e5e7eb; padding: 1rem 0; margin-top: 2rem; text-align: center; color: #6b7280; font-size: 0.9rem;">
        <div class="footer-content" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
            <p>&copy; <?= date('Y') ?> Pengadilan Tata Usaha Negara Banjarmasin. Sistem E-Surat.</p>
            <p style="margin: 0.5rem 0 0; font-size: 0.8rem;">
                Dikembangkan untuk meningkatkan efisiensi pengelolaan surat menyurat
            </p>
        </div>
    </footer>
</body>
</html>