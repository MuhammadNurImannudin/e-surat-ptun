</div>
        </main>
    </div>

    <!-- Enhanced JavaScript -->
    <script src="../../assets/js/script.js"></script>
    
    <!-- Additional Enhanced Scripts -->
    <script>
        // Enhanced functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all enhanced components
            initEnhancedSidebar();
            initEnhancedNotifications();
            initEnhancedAnimations();
            initEnhancedInteractions();
            updateDateTime();
            
            // Update time every second
            setInterval(updateDateTime, 1000);
            
            // Show page loaded notification after delay
            setTimeout(() => {
                showEnhancedNotification('Halaman berhasil dimuat', 'success', 3000);
            }, 800);
        });

        // Enhanced Sidebar functionality
        function initEnhancedSidebar() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                    
                    // Animate toggle button
                    const icon = this.querySelector('i');
                    icon.style.transform = 'rotate(90deg)';
                    setTimeout(() => {
                        icon.style.transform = '';
                    }, 300);
                    
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
            
            // Enhanced mobile handling
            function handleMobileView() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                }
            }
            
            handleMobileView();
            window.addEventListener('resize', handleMobileView);
            
            // Close sidebar on mobile when clicking outside
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                        sidebar.classList.add('collapsed');
                        mainContent.classList.add('expanded');
                    }
                }
            });
        }

        // Enhanced date time update
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

        // Loading overlay functions
        function showLoading(message = 'Memuat...') {
            const overlay = document.getElementById('loadingOverlay');
            const text = overlay.querySelector('p');
            if (text) {
                text.textContent = message;
            }
            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function hideLoading() {
            const overlay = document.getElementById('loadingOverlay');
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }

        // Enhanced form validation
        function validateForm(form) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                clearFieldError(field);
                
                if (!field.value.trim()) {
                    showFieldError(field, 'Field ini wajib diisi');
                    isValid = false;
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
            
            // Date validation
            const dateFields = form.querySelectorAll('input[type="date"]');
            dateFields.forEach(field => {
                if (field.name === 'tanggal_diterima' && field.value) {
                    const tanggalSurat = form.querySelector('input[name="tanggal_surat"]');
                    if (tanggalSurat && tanggalSurat.value) {
                        const tglSurat = new Date(tanggalSurat.value);
                        const tglDiterima = new Date(field.value);
                        
                        if (tglDiterima < tglSurat) {
                            showFieldError(field, 'Tanggal diterima tidak boleh lebih awal dari tanggal surat');
                            isValid = false;
                        }
                    }
                }
            });
            
            return isValid;
        }

        function showFieldError(field, message) {
            clearFieldError(field);
            
            field.style.borderColor = 'var(--danger-color)';
            field.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
            
            const error = document.createElement('div');
            error.className = 'field-error';
            error.style.cssText = `
                color: var(--danger-color);
                font-size: 0.8rem;
                margin-top: 0.25rem;
                display: flex;
                align-items: center;
                gap: 0.25rem;
                animation: slideInDown 0.3s ease;
            `;
            error.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
            
            field.parentNode.appendChild(error);
            
            // Add shake animation
            field.style.animation = 'shake 0.5s ease-in-out';
            setTimeout(() => {
                field.style.animation = '';
            }, 500);
        }

        function clearFieldError(field) {
            field.style.borderColor = '';
            field.style.boxShadow = '';
            
            const error = field.parentNode.querySelector('.field-error');
            if (error) {
                error.remove();
            }
        }

        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Enhanced AJAX helper
        function sendAjaxRequest(url, method = 'GET', data = null, callback = null) {
            showLoading('Mengirim data...');
            
            const xhr = new XMLHttpRequest();
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    hideLoading();
                    
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (callback) callback(null, response);
                            showEnhancedNotification('Operasi berhasil', 'success');
                        } catch (e) {
                            if (callback) callback(null, xhr.responseText);
                        }
                    } else {
                        if (callback) callback(new Error(`Request failed: ${xhr.status}`), null);
                        showEnhancedNotification('Terjadi kesalahan pada server', 'error');
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

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Print functionality
        function printPage() {
            window.print();
        }

        // Refresh dashboard
        function refreshDashboard() {
            showLoading('Memperbarui dashboard...');
            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        // Export functionality
        function exportData(format, type = 'all') {
            showLoading(`Mengekspor data dalam format ${format.toUpperCase()}...`);
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export.php';
            form.style.display = 'none';
            
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
                showEnhancedNotification(`Data berhasil diekspor dalam format ${format.toUpperCase()}`, 'success');
            }, 2000);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + S untuk save
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                const form = document.querySelector('form');
                if (form) {
                    form.dispatchEvent(new Event('submit'));
                }
            }
            
            // Escape untuk close modal atau dropdown
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal.show');
                if (openModal) {
                    hideModal(openModal);
                }
                
                // Close user dropdown
                const dropdown = document.getElementById('userDropdown');
                if (dropdown && dropdown.style.opacity === '1') {
                    toggleUserMenu();
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
            
            // F5 untuk refresh
            if (e.key === 'F5') {
                e.preventDefault();
                refreshDashboard();
            }
        });

        // Enhanced error handling
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.error);
            showEnhancedNotification('Terjadi kesalahan pada sistem. Silakan refresh halaman.', 'error', 0);
        });

        // Connection status monitoring
        window.addEventListener('online', function() {
            showEnhancedNotification('Koneksi internet kembali tersedia', 'success', 3000);
        });

        window.addEventListener('offline', function() {
            showEnhancedNotification('Tidak ada koneksi internet. Beberapa fitur mungkin tidak berfungsi.', 'warning', 0);
        });

        // Auto-save functionality for forms
        function initAutoSave(formId) {
            const form = document.getElementById(formId);
            if (!form) return;
            
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('input', debounce(() => {
                    const formData = new FormData(form);
                    const data = {};
                    for (let [key, value] of formData.entries()) {
                        if (key !== 'file_surat') { // Don't save file data
                            data[key] = value;
                        }
                    }
                    localStorage.setItem(`form_draft_${formId}`, JSON.stringify(data));
                    
                    // Show subtle indication
                    const indicator = document.createElement('div');
                    indicator.style.cssText = `
                        position: fixed;
                        bottom: 20px;
                        right: 20px;
                        background: var(--success-color);
                        color: white;
                        padding: 0.5rem 1rem;
                        border-radius: 0.5rem;
                        font-size: 0.8rem;
                        z-index: 9999;
                        opacity: 0;
                        transition: opacity 0.3s ease;
                    `;
                    indicator.innerHTML = '<i class="fas fa-check"></i> Draft tersimpan';
                    
                    document.body.appendChild(indicator);
                    setTimeout(() => indicator.style.opacity = '1', 100);
                    setTimeout(() => {
                        indicator.style.opacity = '0';
                        setTimeout(() => indicator.remove(), 300);
                    }, 2000);
                }, 1000));
            });
        }

        // Service Worker registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('SW registered: ', registration);
                    })
                    .catch(function(registrationError) {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }

        // Global app utilities
        window.AppUtils = {
            showEnhancedNotification,
            removeEnhancedNotification,
            showLoading,
            hideLoading,
            validateForm,
            sendAjaxRequest,
            debounce,
            formatFileSize,
            printPage,
            refreshDashboard,
            exportData,
            initAutoSave
        };

        // Page transition effects
        function initPageTransitions() {
            // Smooth page transitions
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a[href]:not([target="_blank"]):not([href^="#"]):not([href^="mailto:"]):not([href^="tel:"])');
                
                if (link && !e.ctrlKey && !e.metaKey) {
                    const href = link.getAttribute('href');
                    
                    // Skip external links and special cases
                    if (href.startsWith('http') || href.includes('logout') || href.includes('delete')) {
                        return;
                    }
                    
                    e.preventDefault();
                    
                    // Add loading state
                    showLoading('Memuat halaman...');
                    
                    // Animate page out
                    document.querySelector('.content').style.opacity = '0.3';
                    
                    setTimeout(() => {
                        window.location.href = href;
                    }, 300);
                }
            });
        }

        // Initialize page transitions
        initPageTransitions();

        // Add CSS animations
        const animationStyles = `
            <style>
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    25% { transform: translateX(-5px); }
                    75% { transform: translateX(5px); }
                }
                
                @keyframes slideInDown {
                    from {
                        opacity: 0;
                        transform: translateY(-10px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                
                @keyframes pulse {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.7; }
                }
                
                .user-dropdown a:hover {
                    background: #f8fafc !important;
                    color: var(--primary-color) !important;
                }
                
                .loading-spinner {
                    animation: spin 1s linear infinite;
                }
                
                .content {
                    transition: opacity 0.3s ease;
                }
                
                /* Enhanced scrollbar for webkit browsers */
                ::-webkit-scrollbar {
                    width: 8px;
                }
                
                ::-webkit-scrollbar-track {
                    background: #f1f5f9;
                    border-radius: 4px;
                }
                
                ::-webkit-scrollbar-thumb {
                    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
                    border-radius: 4px;
                }
                
                ::-webkit-scrollbar-thumb:hover {
                    background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
                }
                
                /* Enhanced focus states */
                .form-control:focus,
                .btn:focus,
                .nav-link:focus {
                    outline: 2px solid var(--primary-color);
                    outline-offset: 2px;
                }
                
                /* Enhanced mobile responsive */
                @media (max-width: 480px) {
                    .page-title {
                        font-size: 1.5rem;
                    }
                    
                    .sidebar-header {
                        padding: 1rem;
                    }
                    
                    .logo {
                        width: 50px;
                        height: 50px;
                        font-size: 1.5rem;
                    }
                    
                    .content {
                        padding: 1rem;
                    }
                }
                
                /* Dark mode support */
                @media (prefers-color-scheme: dark) {
                    :root {
                        --dark-color: #f9fafb;
                        --light-color: #1f2937;
                    }
                }
                
                /* Reduced motion for accessibility */
                @media (prefers-reduced-motion: reduce) {
                    *,
                    *::before,
                    *::after {
                        animation-duration: 0.01ms !important;
                        animation-iteration-count: 1 !important;
                        transition-duration: 0.01ms !important;
                    }
                }
            </style>
        `;
        
        document.head.insertAdjacentHTML('beforeend', animationStyles);
    </script>

    <!-- Footer -->
    <footer class="app-footer" style="background: linear-gradient(135deg, white 0%, #f8fafc 100%); border-top: 1px solid #e5e7eb; padding: 2rem 0; margin-top: 3rem; text-align: center; color: #6b7280;">
        <div class="footer-content" style="max-width: 1200px; margin: 0 auto; padding: 0 2rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                
                <!-- About Section -->
                <div style="text-align: left;">
                    <h4 style="color: var(--dark-color); margin-bottom: 1rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-balance-scale" style="color: var(--primary-color);"></i>
                        E-Surat PTUN
                    </h4>
                    <p style="line-height: 1.6; margin-bottom: 1rem; font-size: 0.9rem;">
                        Sistem informasi untuk pengelolaan surat masuk dan surat keluar 
                        Pengadilan Tata Usaha Negara Banjarmasin yang modern dan efisien.
                    </p>
                    <div style="display: flex; gap: 0.75rem;">
                        <div style="width: 32px; height: 32px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; cursor: pointer; transition: transform 0.3s ease;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div style="width: 32px; height: 32px; background: var(--success-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; cursor: pointer; transition: transform 0.3s ease;">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div style="width: 32px; height: 32px; background: var(--secondary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; cursor: pointer; transition: transform 0.3s ease;">
                            <i class="fas fa-globe"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div style="text-align: left;">
                    <h4 style="color: var(--dark-color); margin-bottom: 1rem; font-weight: 700;">Tautan Cepat</h4>
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9rem;">
                        <li style="margin-bottom: 0.5rem;">
                            <a href="../../index.php" style="color: #6b7280; text-decoration: none; transition: color 0.3s ease; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-tachometer-alt" style="width: 14px;"></i>
                                Dashboard
                            </a>
                        </li>
                        <li style="margin-bottom: 0.5rem;">
                            <a href="../../pages/surat-masuk/index.php" style="color: #6b7280; text-decoration: none; transition: color 0.3s ease; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-inbox" style="width: 14px;"></i>
                                Surat Masuk
                            </a>
                        </li>
                        <li style="margin-bottom: 0.5rem;">
                            <a href="../../pages/surat-keluar/index.php" style="color: #6b7280; text-decoration: none; transition: color 0.3s ease; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-paper-plane" style="width: 14px;"></i>
                                Surat Keluar
                            </a>
                        </li>
                        <li>
                            <a href="../../pages/reports/report-rekap.php" style="color: #6b7280; text-decoration: none; transition: color 0.3s ease; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-chart-bar" style="width: 14px;"></i>
                                Laporan
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div style="text-align: left;">
                    <h4 style="color: var(--dark-color); margin-bottom: 1rem; font-weight: 700;">Kontak</h4>
                    <div style="font-size: 0.9rem; line-height: 1.8;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                            <i class="fas fa-map-marker-alt" style="color: var(--primary-color); width: 16px;"></i>
                            <span>Jl. Brig Jend. Hasan Basry<br>Pangeran, Banjarmasin</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                            <i class="fas fa-phone" style="color: var(--success-color); width: 16px;"></i>
                            <span>(0511) 3252747</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-envelope" style="color: var(--warning-color); width: 16px;"></i>
                            <span>ptun.banjarmasin@mahkamahagung.go.id</span>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Copyright -->
            <div style="border-top: 1px solid #e5e7eb; padding-top: 1.5rem; display: flex; justify-content: center; align-items: center; flex-wrap: wrap; gap: 2rem;">
                <p style="margin: 0; font-size: 0.9rem;">
                    &copy; <?= date('Y') ?> Pengadilan Tata Usaha Negara Banjarmasin. 
                    <span style="color: var(--primary-color); font-weight: 600;">Sistem E-Surat</span>.
                </p>
                <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: #9ca3af;">
                    <i class="fas fa-code"></i>
                    <span>Dikembangkan dengan ❤️ untuk PTUN Banjarmasin</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Additional CSS for footer animations -->
    <style>
        .footer-content a:hover {
            color: var(--primary-color) !important;
            transform: translateX(3px);
        }
        
        .footer-content div[style*="border-radius: 50%"]:hover {
            transform: scale(1.1) rotate(5deg);
        }
        
        @media (max-width: 768px) {
            .app-footer {
                padding: 1.5rem 0;
            }
            
            .footer-content > div:first-child {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                text-align: center;
            }
            
            .footer-content div[style*="text-align: left"] {
                text-align: center !important;
            }
            
            .footer-content div[style*="justify-content: center"] {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>

</body>
</html>

        // Enhanced User Menu
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            const chevron = document.querySelector('.user-info i');
            const isOpen = dropdown.style.opacity === '1';
            
            if (isOpen) {
                dropdown.style.opacity = '0';
                dropdown.style.visibility = 'hidden';
                dropdown.style.transform = 'translateY(-10px)';
                chevron.style.transform = 'rotate(0deg)';
            } else {
                dropdown.style.opacity = '1';
                dropdown.style.visibility = 'visible';
                dropdown.style.transform = 'translateY(0)';
                chevron.style.transform = 'rotate(180deg)';
            }
        }

        // Close user menu when clicking outside
        document.addEventListener('click', function(e) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('userDropdown');
            const chevron = document.querySelector('.user-info i');
            
            if (!userMenu.contains(e.target)) {
                dropdown.style.opacity = '0';
                dropdown.style.visibility = 'hidden';
                dropdown.style.transform = 'translateY(-10px)';
                chevron.style.transform = 'rotate(0deg)';
            }
        });

        // Enhanced notification system
        function showEnhancedNotification(message, type = 'info', duration = 5000, title = null) {
            const container = document.getElementById('notificationContainer');
            const notification = document.createElement('div');
            
            notification.className = `notification ${type}`;
            
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            
            const colors = {
                success: '#10b981',
                error: '#ef4444',
                warning: '#f59e0b',
                info: '#3b82f6'
            };
            
            const titles = {
                success: title || 'Berhasil',
                error: title || 'Error',
                warning: title || 'Peringatan',
                info: title || 'Informasi'
            };
            
            notification.innerHTML = `
                <div style="display: flex; align-items: flex-start; gap: 1rem; padding: 1.25rem;">
                    <div style="flex-shrink: 0; width: 32px; height: 32px; border-radius: 50%; background: ${colors[type]}; display: flex; align-items: center; justify-content: center; color: white;">
                        <i class="fas ${icons[type]}" style="font-size: 0.9rem;"></i>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 600; color: var(--dark-color); margin-bottom: 0.25rem;">${titles[type]}</div>
                        <div style="color: #6b7280; font-size: 0.9rem; line-height: 1.4;">${message}</div>
                    </div>
                    <button type="button" onclick="removeEnhancedNotification(this)" style="flex-shrink: 0; background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 1.2rem; padding: 0.25rem; transition: color 0.3s ease;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            container.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Auto remove with progress bar
            if (duration > 0) {
                const progressBar = document.createElement('div');
                progressBar.style.cssText = `
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    height: 3px;
                    background: ${colors[type]};
                    width: 100%;
                    transform-origin: left;
                    animation: shrink ${duration}ms linear forwards;
                `;
                
                notification.style.position = 'relative';
                notification.appendChild(progressBar);
                
                setTimeout(() => {
                    removeEnhancedNotification(notification.querySelector('button'));
                }, duration);
            }
            
            return notification;
        }

        function removeEnhancedNotification(button) {
            const notification = button.closest('.notification');
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 400);
        }

        function initEnhancedNotifications() {
            // Add CSS for progress bar animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes shrink {
                    from { transform: scaleX(1); }
                    to { transform: scaleX(0); }
                }
                
                .notification {
                    position: relative;
                    overflow: hidden;
                }
                
                .notification::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 1px;
                    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
                    animation: shimmer 2s infinite;
                }
                
                @keyframes shimmer {
                    0% { transform: translateX(-100%); }
                    100% { transform: translateX(100%); }
                }
            `;
            document.head.appendChild(style);
        }

        // Enhanced animations
        function initEnhancedAnimations() {
            // Animate elements on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);
            
            // Observe cards and other elements
            document.querySelectorAll('.card, .alert, .stat-card').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        }

        // Enhanced interactions
        function initEnhancedInteractions() {
            // Enhanced button interactions
            document.querySelectorAll('.btn').forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px) scale(1.02)';
                });
                
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                });
                
                btn.addEventListener('mousedown', function() {
                    this.style.transform = 'translateY(-1px) scale(0.98)';
                });
                
                btn.addEventListener('mouseup', function() {
                    this.style.transform = 'translateY(-3px) scale(1.02)';
                });
            });
            
            // Enhanced card interactions
            document.querySelectorAll('.card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                    this.style.boxShadow = '0 25px 50px rgba(0, 0, 0, 0.15)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                    this.style.boxShadow = '';
                });
            });
            
            // Enhanced nav link interactions
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('mouseenter', function() {
                    const icon = this.querySelector('.nav-icon');
                    if (icon) {
                        icon.style.transform = 'scale(1.2) rotate(5deg)';
                    }
                });
                
                link.addEventListener('mouseleave', function() {
                    const icon = this.querySelector('.nav-icon');
                    if (icon) {
                        icon.style.transform = '';
                    }
                });
            });