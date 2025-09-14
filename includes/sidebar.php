<?php
// includes/sidebar.php - Fixed Navigation
// Fixed untuk mengatasi masalah routing dan navigasi

// Get current path untuk menentukan active menu
$current_file = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Function untuk menentukan base URL berdasarkan lokasi file
function getBaseURL() {
    $path = $_SERVER['PHP_SELF'];
    
    // Cek apakah di root directory
    if (strpos($path, '/pages/') !== false) {
        return '../..';
    } elseif (strpos($path, '/includes/') !== false) {
        return '..';
    } else {
        return '.';
    }
}

$baseURL = getBaseURL();

// Function untuk cek active menu
function isActiveMenu($menuPath) {
    global $current_file, $current_dir;
    
    // Extract directory dan file dari menu path
    $pathParts = explode('/', $menuPath);
    $menuDir = isset($pathParts[2]) ? $pathParts[2] : '';
    $menuFile = basename($menuPath);
    
    // Cek untuk dashboard
    if ($menuPath === 'index.php' || $menuPath === '../../index.php') {
        return ($current_file === 'index.php' && $current_dir !== 'surat-masuk' && $current_dir !== 'surat-keluar' && $current_dir !== 'reports');
    }
    
    // Cek untuk directory match
    if ($menuDir === $current_dir) {
        return true;
    }
    
    // Cek untuk file match
    if ($menuFile === $current_file) {
        return true;
    }
    
    return false;
}
?>

<!-- Enhanced Sidebar -->
<nav class="sidebar" id="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <div class="logo-container">
            <div class="logo">
                <i class="fas fa-balance-scale"></i>
            </div>
            <div class="sidebar-title">E-Surat PTUN</div>
            <div class="sidebar-subtitle">Banjarmasin</div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="sidebar-nav">
        <!-- Main Menu -->
        <div class="nav-section">
            <div class="nav-section-title">Menu Utama</div>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="<?= $baseURL ?>/index.php" class="nav-link <?= isActiveMenu('index.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
            </ul>
        </div>

        <!-- Surat Management -->
        <div class="nav-section">
            <div class="nav-section-title">Pengelolaan Surat</div>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="<?= $baseURL ?>/pages/surat-masuk/index.php" class="nav-link <?= isActiveMenu('pages/surat-masuk/index.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-inbox"></i>
                        Surat Masuk
                        <?php if (isset($pending_masuk) && $pending_masuk > 0): ?>
                        <span class="nav-badge" style="margin-left: auto; background: var(--danger-color); color: white; font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 1rem; font-weight: 600;">
                            <?= $pending_masuk ?>
                        </span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $baseURL ?>/pages/surat-keluar/index.php" class="nav-link <?= isActiveMenu('pages/surat-keluar/index.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-paper-plane"></i>
                        Surat Keluar
                        <?php if (isset($draft_keluar) && $draft_keluar > 0): ?>
                        <span class="nav-badge" style="margin-left: auto; background: var(--warning-color); color: white; font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 1rem; font-weight: 600;">
                            <?= $draft_keluar ?>
                        </span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Reports Section -->
        <div class="nav-section">
            <div class="nav-section-title">Laporan & Analisis</div>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="<?= $baseURL ?>/pages/reports/report-surat-masuk.php" class="nav-link <?= isActiveMenu('pages/reports/report-surat-masuk.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-chart-line"></i>
                        Laporan Surat Masuk
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $baseURL ?>/pages/reports/report-surat-keluar.php" class="nav-link <?= isActiveMenu('pages/reports/report-surat-keluar.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        Laporan Surat Keluar
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $baseURL ?>/pages/reports/report-bulanan.php" class="nav-link <?= isActiveMenu('pages/reports/report-bulanan.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        Laporan Bulanan
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $baseURL ?>/pages/reports/report-tahunan.php" class="nav-link <?= isActiveMenu('pages/reports/report-tahunan.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-calendar"></i>
                        Laporan Tahunan
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $baseURL ?>/pages/reports/report-rekap.php" class="nav-link <?= isActiveMenu('pages/reports/report-rekap.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-file-alt"></i>
                        Rekapitulasi Data
                    </a>
                </li>
            </ul>
        </div>

        <?php if (isset($current_user) && $current_user['level'] === 'admin'): ?>
        <!-- Admin Section -->
        <div class="nav-section">
            <div class="nav-section-title">Administrasi</div>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="<?= $baseURL ?>/pages/users/index.php" class="nav-link <?= isActiveMenu('pages/users/index.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        Manajemen User
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $baseURL ?>/pages/settings/index.php" class="nav-link <?= isActiveMenu('pages/settings/index.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-cog"></i>
                        Pengaturan Sistem
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $baseURL ?>/pages/backup/index.php" class="nav-link <?= isActiveMenu('pages/backup/index.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-database"></i>
                        Backup Data
                    </a>
                </li>
            </ul>
        </div>
        <?php endif; ?>

        <!-- User Section -->
        <div class="nav-section">
            <div class="nav-section-title">Akun Pengguna</div>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="<?= $baseURL ?>/pages/profile/index.php" class="nav-link <?= isActiveMenu('pages/profile/index.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-user"></i>
                        Profil Saya
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $baseURL ?>/pages/help/index.php" class="nav-link <?= isActiveMenu('pages/help/index.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-question-circle"></i>
                        Bantuan
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $baseURL ?>/logout.php" class="nav-link" onclick="return confirmLogout()">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        Keluar Sistem
                    </a>
                </li>
            </ul>
        </div>

        <!-- Quick Stats (Optional) -->
        <div class="nav-section">
            <div class="nav-section-title">Statistik Cepat</div>
            <div class="quick-stats" style="padding: 1rem 1.5rem;">
                
                <!-- Total Surat Masuk -->
                <div class="quick-stat-item" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding: 0.75rem; background: rgba(59, 130, 246, 0.1); border-radius: 0.5rem; border-left: 3px solid var(--secondary-color);">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-inbox" style="color: var(--secondary-color);"></i>
                        <span style="font-size: 0.85rem; color: rgba(255, 255, 255, 0.9);">Surat Masuk</span>
                    </div>
                    <span style="font-weight: 700; color: var(--secondary-color); font-size: 1.1rem;" id="totalSuratMasuk">
                        <?php
                        if (isset($pdo)) {
                            try {
                                $stmt = $pdo->query("SELECT COUNT(*) as total FROM surat_masuk");
                                echo $stmt->fetch()['total'] ?? 0;
                            } catch (Exception $e) {
                                echo '0';
                            }
                        } else {
                            echo '0';
                        }
                        ?>
                    </span>
                </div>

                <!-- Total Surat Keluar -->
                <div class="quick-stat-item" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding: 0.75rem; background: rgba(16, 185, 129, 0.1); border-radius: 0.5rem; border-left: 3px solid var(--success-color);">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-paper-plane" style="color: var(--success-color);"></i>
                        <span style="font-size: 0.85rem; color: rgba(255, 255, 255, 0.9);">Surat Keluar</span>
                    </div>
                    <span style="font-weight: 700; color: var(--success-color); font-size: 1.1rem;" id="totalSuratKeluar">
                        <?php
                        if (isset($pdo)) {
                            try {
                                $stmt = $pdo->query("SELECT COUNT(*) as total FROM surat_keluar");
                                echo $stmt->fetch()['total'] ?? 0;
                            } catch (Exception $e) {
                                echo '0';
                            }
                        } else {
                            echo '0';
                        }
                        ?>
                    </span>
                </div>

                <!-- Pending Action -->
                <div class="quick-stat-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: rgba(245, 158, 11, 0.1); border-radius: 0.5rem; border-left: 3px solid var(--warning-color);">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-exclamation-triangle" style="color: var(--warning-color);"></i>
                        <span style="font-size: 0.85rem; color: rgba(255, 255, 255, 0.9);">Pending</span>
                    </div>
                    <span style="font-weight: 700; color: var(--warning-color); font-size: 1.1rem;" id="totalPending">
                        <?php
                        if (isset($pdo)) {
                            try {
                                $stmt = $pdo->query("SELECT COUNT(*) as total FROM surat_masuk WHERE status = 'Masuk'");
                                $masuk = $stmt->fetch()['total'] ?? 0;
                                $stmt = $pdo->query("SELECT COUNT(*) as total FROM surat_keluar WHERE status = 'Draft'");
                                $draft = $stmt->fetch()['total'] ?? 0;
                                echo $masuk + $draft;
                            } catch (Exception $e) {
                                echo '0';
                            }
                        } else {
                            echo '0';
                        }
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer" style="padding: 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.1); margin-top: auto; background: rgba(0, 0, 0, 0.2);">
        <?php if (isset($current_user)): ?>
        <div class="user-info-sidebar" style="text-align: center; color: rgba(255, 255, 255, 0.9);">
            <div class="user-avatar-small" style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--accent-color) 0%, #f59e0b 100%); border-radius: 50%; margin: 0 auto 0.75rem; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem; color: var(--dark-color); border: 2px solid rgba(251, 191, 36, 0.5);">
                <?= strtoupper(substr($current_user['nama_lengkap'] ?? 'User', 0, 1)) ?>
            </div>
            <div class="user-name" style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.25rem;">
                <?= escape(explode(' ', $current_user['nama_lengkap'] ?? 'User')[0]) ?>
            </div>
            <div class="user-role" style="font-size: 0.75rem; opacity: 0.8; text-transform: capitalize; margin-bottom: 0.5rem;">
                <?= ucfirst($current_user['level'] ?? 'user') ?>
            </div>
            <div class="online-status" style="font-size: 0.7rem; opacity: 0.7; display: flex; align-items: center; justify-content: center; gap: 0.25rem;">
                <div style="width: 6px; height: 6px; background: #10b981; border-radius: 50%; animation: pulse 2s infinite;"></div>
                Online
            </div>
        </div>
        <?php endif; ?>
        
        <!-- System Info -->
        <div class="system-info" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.1); text-align: center; font-size: 0.7rem; opacity: 0.6;">
            <div>E-Surat PTUN v1.0.0</div>
            <div class="current-datetime" style="margin-top: 0.25rem;"></div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<main class="main-content" id="mainContent">
    <!-- Enhanced Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-left">
                <button type="button" class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="breadcrumb">
                    <?php 
                    // Simplified breadcrumb - hanya menampilkan path tanpa redundant items
                    if ($current_dir == 'surat-masuk') {
                        if ($current_file == 'tambah.php') {
                            echo '<span><i class="fas fa-inbox"></i> Surat Masuk</span> <i class="fas fa-chevron-right"></i> <span>Tambah</span>';
                        } elseif ($current_file == 'edit.php') {
                            echo '<span><i class="fas fa-inbox"></i> Surat Masuk</span> <i class="fas fa-chevron-right"></i> <span>Edit</span>';
                        } else {
                            echo '<span><i class="fas fa-inbox"></i> Surat Masuk</span>';
                        }
                    } elseif ($current_dir == 'surat-keluar') {
                        if ($current_file == 'tambah.php') {
                            echo '<span><i class="fas fa-paper-plane"></i> Surat Keluar</span> <i class="fas fa-chevron-right"></i> <span>Tambah</span>';
                        } else {
                            echo '<span><i class="fas fa-paper-plane"></i> Surat Keluar</span>';
                        }
                    } elseif ($current_dir == 'reports') {
                        echo '<span><i class="fas fa-chart-line"></i> Laporan</span>';
                    } elseif ($current_dir == 'users') {
                        echo '<span><i class="fas fa-users"></i> Manajemen User</span>';
                    } elseif ($current_dir == 'profile') {
                        echo '<span><i class="fas fa-user"></i> Profil</span>';
                    } else {
                        echo '<span><i class="fas fa-tachometer-alt"></i> Dashboard</span>';
                    }
                    ?>
                </div>
            </div>
            
            <!-- PTUN Info Center -->
            <div class="ptun-info" style="text-align: center; flex: 1;">
                <div class="ptun-title" style="font-size: 1.1rem; font-weight: 700; color: var(--primary-color); margin: 0;">
                    Pengadilan Tata Usaha Negara Banjarmasin
                </div>
                <div class="ptun-subtitle" style="font-size: 0.85rem; color: #6b7280; margin: 0; font-weight: 500;">
                    Sistem Elektronik Surat Menyurat
                </div>
                <div class="current-time" style="font-size: 0.8rem; color: #9ca3af; margin-top: 0.25rem;">
                    <i class="fas fa-clock"></i>
                    <span class="current-datetime"></span>
                </div>
            </div>
            
            <div class="header-right">
                <!-- Notifications -->
                <div class="notifications-container">
                    <button type="button" class="notification-bell" id="notificationToggle" title="Notifikasi" style="background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%); border: none; width: 48px; height: 48px; border-radius: 50%; color: white; cursor: pointer; position: relative; transition: all 0.3s ease; box-shadow: var(--shadow); font-size: 1.2rem;">
                        <i class="fas fa-bell"></i>
                        <?php
                        $total_notifications = 0;
                        if (isset($pdo)) {
                            try {
                                $stmt = $pdo->query("SELECT COUNT(*) as pending FROM surat_masuk WHERE status = 'Masuk'");
                                $pending = $stmt->fetch()['pending'] ?? 0;
                                $stmt = $pdo->query("SELECT COUNT(*) as draft FROM surat_keluar WHERE status = 'Draft'");
                                $draft = $stmt->fetch()['draft'] ?? 0;
                                $total_notifications = $pending + $draft;
                            } catch (Exception $e) {
                                $total_notifications = 0;
                            }
                        }
                        
                        if ($total_notifications > 0):
                        ?>
                        <span class="notification-badge" style="position: absolute; top: -5px; right: -5px; background: var(--danger-color); color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; animation: pulse 2s infinite;">
                            <?= $total_notifications ?>
                        </span>
                        <?php endif; ?>
                    </button>
                </div>

                <!-- User Menu -->
                <?php if (isset($current_user)): ?>
                <div class="user-menu">
                    <div class="user-info" id="userMenuToggle" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 0.75rem; background: linear-gradient(135deg, white 0%, #f8fafc 100%); border: 1px solid #e5e7eb; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: var(--shadow);">
                        <div class="user-avatar" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1rem; box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);">
                            <?= strtoupper(substr($current_user['nama_lengkap'], 0, 1)) ?>
                        </div>
                        <div class="user-details" style="display: flex; flex-direction: column; align-items: flex-start;">
                            <span class="user-name" style="font-weight: 600; font-size: 0.95rem; color: var(--dark-color);">
                                <?= escape($current_user['nama_lengkap']) ?>
                            </span>
                            <span class="user-role" style="font-size: 0.8rem; color: #6b7280; text-transform: capitalize;">
                                <?= escape($current_user['jabatan']) ?>
                            </span>
                        </div>
                        <i class="fas fa-chevron-down user-chevron" style="transition: transform 0.3s ease; color: #9ca3af;"></i>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Content Area -->
    <div class="content">
        <!-- Page Header with Actions -->
        <div class="page-header">
            <div class="page-header-content" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                <div class="header-left">
                    <h1 class="page-title" style="font-size: 2.25rem; font-weight: 800; color: var(--dark-color); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 1rem;">
                        <?php
                        // Dynamic page title based on current location
                        switch ($current_dir) {
                            case 'surat-masuk':
                                echo '<i class="fas fa-inbox text-blue-600"></i> ';
                                if ($current_file == 'tambah.php') {
                                    echo 'Tambah Surat Masuk';
                                } elseif ($current_file == 'edit.php') {
                                    echo 'Edit Surat Masuk';
                                } elseif ($current_file == 'detail.php') {
                                    echo 'Detail Surat Masuk';
                                } else {
                                    echo 'Data Surat Masuk';
                                }
                                break;
                            case 'surat-keluar':
                                echo '<i class="fas fa-paper-plane text-green-600"></i> ';
                                if ($current_file == 'tambah.php') {
                                    echo 'Tambah Surat Keluar';
                                } else {
                                    echo 'Data Surat Keluar';
                                }
                                break;
                            case 'reports':
                                echo '<i class="fas fa-chart-line text-purple-600"></i> ';
                                if (strpos($current_file, 'bulanan') !== false) {
                                    echo 'Laporan Bulanan';
                                } elseif (strpos($current_file, 'tahunan') !== false) {
                                    echo 'Laporan Tahunan';
                                } elseif (strpos($current_file, 'rekap') !== false) {
                                    echo 'Rekapitulasi Data';
                                } elseif (strpos($current_file, 'surat-masuk') !== false) {
                                    echo 'Laporan Surat Masuk';
                                } elseif (strpos($current_file, 'surat-keluar') !== false) {
                                    echo 'Laporan Surat Keluar';
                                } else {
                                    echo 'Laporan & Analisis';
                                }
                                break;
                            case 'users':
                                echo '<i class="fas fa-users text-indigo-600"></i> Manajemen User';
                                break;
                            case 'profile':
                                echo '<i class="fas fa-user text-gray-600"></i> Profil Pengguna';
                                break;
                            default:
                                echo '<i class="fas fa-tachometer-alt text-blue-600"></i> Dashboard E-Surat';
                        }
                        ?>
                    </h1>
                    <p class="page-description" style="color: #6b7280; font-size: 1.1rem; font-weight: 400;">
                        <?php
                        // Dynamic description
                        switch ($current_dir) {
                            case 'surat-masuk':
                                if ($current_file == 'tambah.php') {
                                    echo 'Formulir input data surat masuk baru ke dalam sistem PTUN Banjarmasin';
                                } else {
                                    echo 'Kelola dan pantau semua surat masuk yang diterima PTUN Banjarmasin';
                                }
                                break;
                            case 'surat-keluar':
                                if ($current_file == 'tambah.php') {
                                    echo 'Formulir pembuatan surat keluar baru dari PTUN Banjarmasin';
                                } else {
                                    echo 'Kelola dan pantau semua surat keluar yang dikirim PTUN Banjarmasin';
                                }
                                break;
                            case 'reports':
                                echo 'Laporan komprehensif dan analisis data surat masuk & keluar PTUN Banjarmasin';
                                break;
                            case 'users':
                                echo 'Kelola pengguna sistem dan hak akses E-Surat PTUN Banjarmasin';
                                break;
                            case 'profile':
                                echo 'Informasi profil dan pengaturan akun pengguna sistem';
                                break;
                            default:
                                echo 'Pusat kontrol dan ringkasan aktivitas E-Surat PTUN Banjarmasin';
                        }
                        ?>
                    </p>
                </div>
                
                <!-- Quick Action Buttons -->
                <div class="page-header-actions" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <?php if ($current_dir == 'surat-masuk' && $current_file == 'index.php'): ?>
                    <a href="tambah.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tambah Surat Masuk
                    </a>
                    <button class="btn btn-success" onclick="exportData('excel')">
                        <i class="fas fa-file-excel"></i>
                        Export Excel
                    </button>
                    <?php elseif ($current_dir == 'surat-keluar' && $current_file == 'index.php'): ?>
                    <a href="tambah.php" class="btn btn-success">
                        <i class="fas fa-plus"></i>
                        Buat Surat Keluar
                    </a>
                    <button class="btn btn-primary" onclick="exportData('pdf')">
                        <i class="fas fa-file-pdf"></i>
                        Export PDF
                    </button>
                    <?php elseif ($current_file == 'index.php' && $current_dir == ''): ?>
                    <button class="btn btn-primary" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i>
                        Refresh Data
                    </button>
                    <button class="btn btn-secondary" onclick="printPage()">
                        <i class="fas fa-print"></i>
                        Print Dashboard
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php
        $alert_types = ['success', 'error', 'warning', 'info'];
        foreach ($alert_types as $type) {
            if (isset($_SESSION[$type])) {
                $icon_map = [
                    'success' => 'fa-check-circle',
                    'error' => 'fa-exclamation-circle', 
                    'warning' => 'fa-exclamation-triangle',
                    'info' => 'fa-info-circle'
                ];
                
                $color_map = [
                    'success' => 'var(--success-color)',
                    'error' => 'var(--danger-color)',
                    'warning' => 'var(--warning-color)', 
                    'info' => 'var(--secondary-color)'
                ];
                
                echo '<div class="alert alert-' . $type . '" style="animation: slideInDown 0.4s cubic-bezier(0.4, 0, 0.2, 1);">';
                echo '<div style="display: flex; align-items: flex-start; gap: 0.75rem;">';
                echo '<i class="fas ' . $icon_map[$type] . '" style="color: ' . $color_map[$type] . '; font-size: 1.2rem; margin-top: 0.1rem;"></i>';
                echo '<div style="flex: 1;">';
                echo '<strong>' . ucfirst($type) . '!</strong>';
                echo '<div>' . escape($_SESSION[$type]) . '</div>';
                echo '</div>';
                echo '<button type="button" onclick="this.parentElement.parentElement.remove()" style="margin-left: auto; background: none; border: none; color: inherit; opacity: 0.7; cursor: pointer; font-size: 1.2rem; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.3s ease;">';
                echo '<i class="fas fa-times"></i>';
                echo '</button>';
                echo '</div>';
                echo '</div>';
                
                unset($_SESSION[$type]);
            }
        }
        ?>

        <!-- Main Content Area Start -->
        <div class="page-content">

<script>
// Enhanced Sidebar Management
document.addEventListener('DOMContentLoaded', function() {
    initEnhancedSidebar();
    initNotificationSystem();
    initUserMenu();
    updateDateTime();
    setInterval(updateDateTime, 1000);
    setInterval(updateQuickStats, 60000); // Update stats every minute
});

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
            icon.style.transform = 'rotate(180deg)';
            setTimeout(() => {
                icon.style.transform = '';
            }, 400);
            
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
    
    // Enhanced nav link interactions
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach((link, index) => {
        // Add loading effect on click
        link.addEventListener('click', function(e) {
            // Only add loading for actual navigation (not logout)
            if (!this.getAttribute('onclick') && this.getAttribute('href') && !this.getAttribute('href').startsWith('#')) {
                // Add loading indicator
                const originalContent = this.innerHTML;
                this.style.pointerEvents = 'none';
                this.style.opacity = '0.7';
                
                const loadingIcon = this.querySelector('.nav-icon');
                if (loadingIcon) {
                    loadingIcon.className = 'nav-icon fas fa-spinner fa-spin';
                }
                
                // Show global loading
                showLoading('Memuat halaman...');
            }
        });
        
        // Enhanced hover effects with staggered animation
        link.addEventListener('mouseenter', function() {
            const icon = this.querySelector('.nav-icon');
            if (icon && !icon.classList.contains('fa-spin')) {
                icon.style.transform = 'scale(1.2) rotate(10deg)';
                icon.style.color = 'var(--accent-color)';
            }
            
            // Add glow effect
            this.style.boxShadow = 'inset 0 0 20px rgba(251, 191, 36, 0.2)';
        });
        
        link.addEventListener('mouseleave', function() {
            const icon = this.querySelector('.nav-icon');
            if (icon && !icon.classList.contains('fa-spin')) {
                icon.style.transform = '';
                icon.style.color = '';
            }
            
            if (!this.classList.contains('active')) {
                this.style.boxShadow = '';
            }
        });
        
        // Add entrance animation with delay
        link.style.opacity = '0';
        link.style.transform = 'translateX(-20px)';
        link.style.animation = `slideInLeft 0.6s ease forwards ${index * 0.1}s`;
    });
    
    // Mobile handling
    function handleMobileView() {
        if (window.innerWidth <= 768) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        } else {
            const sidebarState = localStorage.getItem('sidebarCollapsed');
            if (sidebarState !== 'true') {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
            }
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

function initNotificationSystem() {
    const notificationToggle = document.getElementById('notificationToggle');
    
    if (notificationToggle) {
        notificationToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Simple notification popup
            const existingPopup = document.querySelector('.notification-popup');
            if (existingPopup) {
                existingPopup.remove();
                return;
            }
            
            const popup = document.createElement('div');
            popup.className = 'notification-popup';
            popup.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
                width: 350px;
                z-index: 9999;
                border: 1px solid #e5e7eb;
                overflow: hidden;
                animation: slideInRight 0.3s ease;
            `;
            
            popup.innerHTML = `
                <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                    <h3 style="margin: 0; display: flex; align-items: center; gap: 0.5rem; color: var(--dark-color); font-weight: 700;">
                        <i class="fas fa-bell" style="color: var(--warning-color);"></i>
                        Notifikasi PTUN
                    </h3>
                </div>
                <div style="max-height: 300px; overflow-y: auto;">
                    <div style="padding: 1.5rem; text-align: center; color: #6b7280;">
                        <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--success-color); margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p style="margin: 0; font-size: 0.95rem;">Semua notifikasi telah dibaca</p>
                        <p style="margin: 0.5rem 0 0; font-size: 0.8rem; opacity: 0.7;">Sistem berjalan dengan baik</p>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popup);
            
            // Close on outside click
            setTimeout(() => {
                document.addEventListener('click', function closePopup(e) {
                    if (!popup.contains(e.target) && e.target !== notificationToggle) {
                        popup.remove();
                        document.removeEventListener('click', closePopup);
                    }
                });
            }, 100);
            
            // Auto close after 10 seconds
            setTimeout(() => {
                if (popup.parentElement) {
                    popup.remove();
                }
            }, 10000);
        });
        
        // Add bell animation
        setInterval(() => {
            notificationToggle.style.animation = 'bellShake 0.5s ease';
            setTimeout(() => {
                notificationToggle.style.animation = '';
            }, 500);
        }, 30000); // Every 30 seconds
    }
}

function initUserMenu() {
    const userMenuToggle = document.getElementById('userMenuToggle');
    
    if (userMenuToggle) {
        userMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Simple user menu
            const existingMenu = document.querySelector('.user-menu-popup');
            if (existingMenu) {
                existingMenu.remove();
                this.querySelector('.user-chevron').style.transform = '';
                return;
            }
            
            this.querySelector('.user-chevron').style.transform = 'rotate(180deg)';
            
            const menu = document.createElement('div');
            menu.className = 'user-menu-popup';
            menu.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
                width: 280px;
                z-index: 9999;
                border: 1px solid #e5e7eb;
                overflow: hidden;
                animation: slideInRight 0.3s ease;
            `;
            
            const baseURL = '<?= $baseURL ?>';
            
            menu.innerHTML = `
                <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.2rem;">
                            <?= isset($current_user) ? strtoupper(substr($current_user['nama_lengkap'], 0, 1)) : 'U' ?>
                        </div>
                        <div>
                            <div style="font-weight: 600; color: var(--dark-color);"><?= isset($current_user) ? escape($current_user['nama_lengkap']) : 'User' ?></div>
                            <div style="font-size: 0.8rem; color: #6b7280;"><?= isset($current_user) ? escape($current_user['jabatan']) : 'Jabatan' ?></div>
                            <div style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.25rem; display: flex; align-items: center; gap: 0.25rem;">
                                <div style="width: 6px; height: 6px; background: #10b981; border-radius: 50%;"></div>
                                Online
                            </div>
                        </div>
                    </div>
                </div>
                <div style="padding: 0.5rem 0;">
                    <a href="${baseURL}/pages/profile/index.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.5rem; color: var(--dark-color); text-decoration: none; transition: background 0.3s ease; font-size: 0.9rem;">
                        <i class="fas fa-user" style="width: 16px; color: var(--primary-color);"></i>
                        Profil Saya
                    </a>
                    <a href="${baseURL}/pages/settings/index.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.5rem; color: var(--dark-color); text-decoration: none; transition: background 0.3s ease; font-size: 0.9rem;">
                        <i class="fas fa-cog" style="width: 16px; color: var(--primary-color);"></i>
                        Pengaturan
                    </a>
                    <div style="height: 1px; background: #e5e7eb; margin: 0.5rem 0;"></div>
                    <a href="${baseURL}/logout.php" onclick="return confirmLogout()" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.5rem; color: var(--danger-color); text-decoration: none; transition: background 0.3s ease; font-size: 0.9rem;">
                        <i class="fas fa-sign-out-alt" style="width: 16px;"></i>
                        Keluar Sistem
                    </a>
                </div>
            `;
            
            // Add hover effects to menu items
            const menuLinks = menu.querySelectorAll('a');
            menuLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.background = '#f8fafc';
                    if (!this.style.color.includes('danger')) {
                        this.style.color = 'var(--primary-color)';
                    }
                });
                
                link.addEventListener('mouseleave', function() {
                    this.style.background = '';
                    if (!this.getAttribute('onclick')) {
                        this.style.color = 'var(--dark-color)';
                    } else {
                        this.style.color = 'var(--danger-color)';
                    }
                });
            });
            
            document.body.appendChild(menu);
            
            // Close on outside click
            setTimeout(() => {
                document.addEventListener('click', function closeMenu(e) {
                    if (!menu.contains(e.target) && e.target !== userMenuToggle && !userMenuToggle.contains(e.target)) {
                        menu.remove();
                        userMenuToggle.querySelector('.user-chevron').style.transform = '';
                        document.removeEventListener('click', closeMenu);
                    }
                });
            }, 100);
        });
    }
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

function updateQuickStats() {
    // Update quick stats in sidebar via AJAX
    fetch('<?= $baseURL ?>/api/get-stats.php')
        .then(response => response.json())
        .then(data => {
            const masukEl = document.getElementById('totalSuratMasuk');
            const keluarEl = document.getElementById('totalSuratKeluar');
            const pendingEl = document.getElementById('totalPending');
            
            if (masukEl && data.total_masuk !== undefined) {
                animateNumber(masukEl, parseInt(masukEl.textContent), data.total_masuk);
            }
            if (keluarEl && data.total_keluar !== undefined) {
                animateNumber(keluarEl, parseInt(keluarEl.textContent), data.total_keluar);
            }
            if (pendingEl && data.total_pending !== undefined) {
                animateNumber(pendingEl, parseInt(pendingEl.textContent), data.total_pending);
            }
        })
        .catch(error => {
            console.log('Stats update error:', error);
        });
}

function animateNumber(element, start, end) {
    if (start === end) return;
    
    const duration = 1000;
    const increment = (end - start) / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        element.textContent = Math.round(current);
    }, 16);
}

function confirmLogout() {
    return confirm('Yakin ingin keluar dari sistem E-Surat PTUN Banjarmasin?\n\nAnda akan dialihkan ke halaman login.');
}

function showLoading(message = 'Memuat...') {
    let overlay = document.getElementById('loadingOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(5px);
        `;
        overlay.innerHTML = `
            <div style="text-align: center;">
                <div style="width: 60px; height: 60px; border: 4px solid #f3f3f3; border-top: 4px solid var(--primary-color); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem;"></div>
                <p style="color: var(--dark-color); font-weight: 600; margin: 0;">${message}</p>
            </div>
        `;
        document.body.appendChild(overlay);
    }
    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function refreshDashboard() {
    showLoading('Memperbarui dashboard...');
    setTimeout(() => {
        location.reload();
    }, 1000);
}

function exportData(format) {
    showLoading(`Mengekspor data ke ${format.toUpperCase()}...`);
    
    // Simulate export process
    setTimeout(() => {
        hideLoading();
        
        // Create download notification
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--success-color);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            z-index: 10000;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            animation: slideInRight 0.3s ease;
        `;
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-check-circle"></i>
                <span>Data berhasil diekspor ke ${format.toUpperCase()}</span>
                <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; cursor: pointer; padding: 0; margin-left: 0.5rem;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }, 2000);
}

function printPage() {
    window.print();
}

// Add enhanced CSS animations
const enhancedStyles = document.createElement('style');
enhancedStyles.textContent = `
    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes bellShake {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-15deg); }
        75% { transform: rotate(15deg); }
    }
    
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .alert {
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }
    
    .alert::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        animation: shimmer 2s infinite;
    }
    
    @keyframes shimmer {
        0% { left: -100%; }
        100% { left: 100%; }
    }
    
    .alert-success {
        background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
        color: #047857;
        border-left: 4px solid var(--success-color);
    }
    
    .alert-error {
        background: linear-gradient(135deg, #fef2f2 0%, #fef2f2 100%);
        color: #dc2626;
        border-left: 4px solid var(--danger-color);
    }
    
    .alert-warning {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        color: #d97706;
        border-left: 4px solid var(--warning-color);
    }
    
    .alert-info {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: #1d4ed8;
        border-left: 4px solid var(--secondary-color);
    }
    
    .page-title {
        background: linear-gradient(135deg, var(--dark-color) 0%, var(--primary-color) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: fadeInUp 0.8s ease;
    }
    
    .page-description {
        animation: fadeInUp 0.8s ease 0.2s both;
    }
    
    .page-header-actions {
        animation: fadeInUp 0.8s ease 0.4s both;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .notification-bell:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.3);
    }
    
    .user-info:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        border-color: var(--primary-color);
    }
    
    .btn {
        position: relative;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .btn:hover::before {
        left: 100%;
    }
    
    .btn:hover {
        transform: translateY(-2px);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border: none;
        color: white;
        box-shadow: 0 4px 6px -1px rgba(30, 58, 138, 0.3);
    }
    
    .btn-primary:hover {
        box-shadow: 0 10px 15px -3px rgba(30, 58, 138, 0.4);
    }
    
    .btn-success {
        background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
        border: none;
        color: white;
        box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
    }
    
    .btn-success:hover {
        box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.4);
    }
    
    .btn-secondary {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        border: none;
        color: white;
        box-shadow: 0 4px 6px -1px rgba(107, 114, 128, 0.3);
    }
    
    .btn-secondary:hover {
        box-shadow: 0 10px 15px -3px rgba(107, 114, 128, 0.4);
    }
    
    /* Responsive improvements */
    @media (max-width: 768px) {
        .page-header-content {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 1rem;
        }
        
        .page-header-actions {
            width: 100%;
            justify-content: flex-start;
            flex-wrap: wrap;
        }
        
        .page-title {
            font-size: 1.75rem !important;
        }
        
        .notification-popup,
        .user-menu-popup {
            width: 300px !important;
            right: 10px !important;
        }
    }
    
    @media (max-width: 480px) {
        .page-title {
            font-size: 1.5rem !important;
        }
        
        .notification-popup,
        .user-menu-popup {
            width: calc(100vw - 20px) !important;
            right: 10px !important;
            left: 10px !important;
        }
        
        .user-details {
            display: none;
        }
        
        .ptun-info {
            display: none;
        }
    }
    
    /* Print styles */
    @media print {
        .sidebar,
        .header,
        .notification-popup,
        .user-menu-popup,
        .btn,
        .alert button {
            display: none !important;
        }
        
        .main-content {
            margin-left: 0 !important;
        }
        
        .content {
            padding: 0 !important;
        }
        
        .alert {
            border: 1px solid #000 !important;
            background: white !important;
        }
        
        .page-title {
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
        }
    }
`;

document.head.appendChild(enhancedStyles);

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Alt + M untuk toggle sidebar
    if (e.altKey && e.key === 'm') {
        e.preventDefault();
        document.getElementById('sidebarToggle').click();
    }
    
    // Alt + N untuk notifikasi
    if (e.altKey && e.key === 'n') {
        e.preventDefault();
        document.getElementById('notificationToggle').click();
    }
    
    // Alt + U untuk user menu
    if (e.altKey && e.key === 'u') {
        e.preventDefault();
        document.getElementById('userMenuToggle').click();
    }
    
    // Escape untuk menutup popup
    if (e.key === 'Escape') {
        const popups =