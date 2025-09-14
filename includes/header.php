<?php
// includes/header.php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/login.php');
    exit();
}

// Get current user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch();
    
    if (!$current_user) {
        session_destroy();
        header('Location: login.php');
        exit();
    }
} catch (PDOException $e) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Get current page info for breadcrumb
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$page_title = 'Dashboard';

switch ($current_page) {
    case 'index':
        $page_title = 'Dashboard';
        $breadcrumb = '<a href="index.php">Dashboard</a>';
        break;
    case 'surat-masuk':
        $page_title = 'Surat Masuk';
        $breadcrumb = '<a href="index.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Surat Masuk';
        break;
    case 'surat-keluar':
        $page_title = 'Surat Keluar';
        $breadcrumb = '<a href="index.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Surat Keluar';
        break;
    case 'tambah':
        if (strpos($_SERVER['PHP_SELF'], 'surat-masuk') !== false) {
            $page_title = 'Tambah Surat Masuk';
            $breadcrumb = '<a href="../index.php">Dashboard</a> <i class="fas fa-chevron-right"></i> <a href="index.php">Surat Masuk</a> <i class="fas fa-chevron-right"></i> Tambah';
        } elseif (strpos($_SERVER['PHP_SELF'], 'surat-keluar') !== false) {
            $page_title = 'Tambah Surat Keluar';
            $breadcrumb = '<a href="../index.php">Dashboard</a> <i class="fas fa-chevron-right"></i> <a href="index.php">Surat Keluar</a> <i class="fas fa-chevron-right"></i> Tambah';
        }
        break;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - E-Surat PTUN Banjarmasin</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- External Libraries -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <!-- Meta Tags -->
    <meta name="description" content="Sistem E-Surat PTUN Banjarmasin - Pengelolaan Surat Masuk dan Surat Keluar">
    <meta name="keywords" content="e-surat, PTUN, Banjarmasin, surat masuk, surat keluar">
    <meta name="author" content="PTUN Banjarmasin">
    
    <!-- Custom Styles for specific pages -->
    <style>
        /* Page specific animations */
        .page-enter {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Enhanced loading states */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .loading-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        /* Enhanced notifications */
        .notification-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 5000;
            max-width: 400px;
        }
        
        .notification {
            background: white;
            border-radius: 0.75rem;
            box-shadow: var(--shadow-lg);
            margin-bottom: 1rem;
            overflow: hidden;
            transform: translateX(100%);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            border-left-color: var(--success-color);
        }
        
        .notification.error {
            border-left-color: var(--danger-color);
        }
        
        .notification.warning {
            border-left-color: var(--warning-color);
        }
        
        .notification-content {
            padding: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .notification-icon {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }
        
        .notification.success .notification-icon {
            background: var(--success-color);
            color: white;
        }
        
        .notification.error .notification-icon {
            background: var(--danger-color);
            color: white;
        }
        
        .notification.warning .notification-icon {
            background: var(--warning-color);
            color: white;
        }
        
        .notification-text {
            flex: 1;
        }
        
        .notification-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--dark-color);
        }
        
        .notification-message {
            font-size: 0.9rem;
            color: #6b7280;
            line-height: 1.4;
        }
        
        .notification-close {
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 0.25rem;
            transition: color 0.3s ease;
        }
        
        .notification-close:hover {
            color: var(--danger-color);
        }
        
        /* Print styles */
        @media print {
            .sidebar,
            .header,
            .notification-container,
            .loading-overlay {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            body {
                background: white !important;
            }
        }
    </style>
</head>
<body class="<?= $current_page ?>-page">
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>
    
    <!-- Notification Container -->
    <div class="notification-container" id="notificationContainer">
        <!-- Notifications will be inserted here -->
    </div>

    <!-- Main Container -->
    <div class="main-container">
        
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <div class="logo">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div class="sidebar-title">E-Surat PTUN</div>
                    <div class="sidebar-subtitle">Banjarmasin</div>
                </div>
            </div>

            <div class="sidebar-nav">
                <!-- Main Navigation -->
                <div class="nav-section">
                    <div class="nav-section-title">Menu Utama</div>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="index.php" class="nav-link <?= $current_page == 'index' ? 'active' : '' ?>">
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
                            <a href="pages/surat-masuk/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'surat-masuk') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-inbox"></i>
                                Surat Masuk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="pages/surat-keluar/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'surat-keluar') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-paper-plane"></i>
                                Surat Keluar
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Reports -->
                <div class="nav-section">
                    <div class="nav-section-title">Laporan</div>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="pages/reports/report-surat-masuk.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'report-surat-masuk') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-chart-line"></i>
                                Laporan Surat Masuk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="pages/reports/report-surat-keluar.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'report-surat-keluar') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                Laporan Surat Keluar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="pages/reports/report-bulanan.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'report-bulanan') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                Laporan Bulanan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="pages/reports/report-tahunan.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'report-tahunan') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-calendar"></i>
                                Laporan Tahunan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="pages/reports/report-rekap.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'report-rekap') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-file-alt"></i>
                                Rekapitulasi
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Settings (Admin Only) -->
                <?php if ($current_user['level'] == 'admin'): ?>
                <div class="nav-section">
                    <div class="nav-section-title">Pengaturan</div>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="pages/users/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-users"></i>
                                Manajemen User
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="pages/settings/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'settings') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-cog"></i>
                                Pengaturan Sistem
                            </a>
                        </li>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- User Section -->
                <div class="nav-section">
                    <div class="nav-section-title">Akun</div>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="pages/profile/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'profile') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-user"></i>
                                Profil Saya
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link" onclick="return confirm('Yakin ingin keluar dari sistem?')">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                Keluar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer" style="padding: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.1); margin-top: auto;">
                <div class="user-info" style="text-align: center; color: rgba(255, 255, 255, 0.8);">
                    <div class="user-avatar" style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; margin: 0 auto 0.5rem; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                        <?= strtoupper(substr($current_user['nama_lengkap'], 0, 1)) ?>
                    </div>
                    <div class="user-name" style="font-size: 0.9rem; font-weight: 500; margin-bottom: 0.25rem;">
                        <?= escape(explode(' ', $current_user['nama_lengkap'])[0]) ?>
                    </div>
                    <div class="user-role" style="font-size: 0.75rem; opacity: 0.8;">
                        <?= ucfirst($current_user['level']) ?>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content" id="mainContent">
            <!-- Header -->
            <header class="header">
                <div class="header-content">
                    <div class="header-left">
                        <button type="button" class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="breadcrumb">
                            <?= $breadcrumb ?? '<a href="index.php">Dashboard</a>' ?>
                        </div>
                    </div>
                    <div class="header-right">
                        <div class="header-info">
                            <div class="current-time">
                                <i class="fas fa-clock"></i>
                                <span class="current-datetime"></span>
                            </div>
                        </div>
                        <div class="user-menu dropdown">
                            <button class="user-info dropdown-toggle" data-toggle="dropdown">
                                <div class="user-avatar">
                                    <?= strtoupper(substr($current_user['nama_lengkap'], 0, 1)) ?>
                                </div>
                                <div class="user-details">
                                    <span class="user-name"><?= escape($current_user['nama_lengkap']) ?></span>
                                    <span class="user-role"><?= escape($current_user['jabatan']) ?></span>
                                </div>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a href="pages/profile/index.php" class="dropdown-item">
                                    <i class="fas fa-user"></i>
                                    Profil Saya
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="logout.php" class="dropdown-item" onclick="return confirm('Yakin ingin keluar dari sistem?')">
                                    <i class="fas fa-sign-out-alt"></i>
                                    Keluar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content page-enter">
                <div class="page-header">
                    <div class="page-header-content">
                        <h1 class="page-title">
                            <?php
                            switch ($current_page) {
                                case 'index':
                                    echo '<i class="fas fa-tachometer-alt"></i> Dashboard';
                                    break;
                                case 'surat-masuk':
                                    echo '<i class="fas fa-inbox"></i> Surat Masuk';
                                    break;
                                case 'surat-keluar':
                                    echo '<i class="fas fa-paper-plane"></i> Surat Keluar';
                                    break;
                                default:
                                    echo '<i class="fas fa-file-alt"></i> ' . $page_title;
                            }
                            ?>
                        </h1>
                        <p class="page-description">
                            <?php
                            switch ($current_page) {
                                case 'index':
                                    echo 'Ringkasan sistem dan aktivitas terbaru E-Surat PTUN Banjarmasin';
                                    break;
                                case 'surat-masuk':
                                    echo 'Kelola dan pantau semua surat masuk yang diterima';
                                    break;
                                case 'surat-keluar':
                                    echo 'Kelola dan pantau semua surat keluar yang dikirim';
                                    break;
                                default:
                                    echo 'Sistem E-Surat PTUN Banjarmasin';
                            }
                            ?>
                        </p>
                    </div>
                    <div class="page-header-actions">
                        <?php if ($current_page == 'index'): ?>
                        <button class="btn btn-primary" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt"></i>
                            Refresh
                        </button>
                        <?php elseif ($current_page == 'surat-masuk'): ?>
                        <a href="tambah.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Tambah Surat Masuk
                        </a>
                        <?php elseif ($current_page == 'surat-keluar'): ?>
                        <a href="tambah.php" class="btn btn-success">
                            <i class="fas fa-plus"></i>
                            Buat Surat Keluar
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <div class="alert-content">
                        <i class="fas fa-check-circle"></i>
                        <span><?= escape($_SESSION['success']) ?></span>
                        <button type="button" class="alert-close" onclick="this.parentElement.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <?php unset($_SESSION['success']); endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <div class="alert-content">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= escape($_SESSION['error']) ?></span>
                        <button type="button" class="alert-close" onclick="this.parentElement.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <?php unset($_SESSION['error']); endif; ?>

                <!-- Main Content Area -->