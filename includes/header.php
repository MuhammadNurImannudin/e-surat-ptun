<?php
// includes/header.php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])) . '/login.php');
    exit();
}

// Get current user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch();
    
    if (!$current_user) {
        session_destroy();
        header('Location: ../../login.php');
        exit();
    }
} catch (PDOException $e) {
    session_destroy();
    header('Location: ../../login.php');
    exit();
}

// Get current page info for breadcrumb
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$page_title = 'Dashboard';

// Build breadcrumb based on current location
$breadcrumb = '<a href="../../index.php"><i class="fas fa-home"></i> Dashboard</a>';

if ($current_dir == 'surat-masuk') {
    $page_title = 'Surat Masuk';
    $breadcrumb .= ' <i class="fas fa-chevron-right"></i> <a href="index.php">Surat Masuk</a>';
    
    if ($current_page == 'tambah') {
        $page_title = 'Tambah Surat Masuk';
        $breadcrumb .= ' <i class="fas fa-chevron-right"></i> <span>Tambah</span>';
    } elseif ($current_page == 'edit') {
        $page_title = 'Edit Surat Masuk';
        $breadcrumb .= ' <i class="fas fa-chevron-right"></i> <span>Edit</span>';
    } elseif ($current_page == 'detail') {
        $page_title = 'Detail Surat Masuk';
        $breadcrumb .= ' <i class="fas fa-chevron-right"></i> <span>Detail</span>';
    }
} elseif ($current_dir == 'surat-keluar') {
    $page_title = 'Surat Keluar';
    $breadcrumb .= ' <i class="fas fa-chevron-right"></i> <a href="index.php">Surat Keluar</a>';
    
    if ($current_page == 'tambah') {
        $page_title = 'Tambah Surat Keluar';
        $breadcrumb .= ' <i class="fas fa-chevron-right"></i> <span>Tambah</span>';
    }
} elseif ($current_dir == 'reports') {
    $page_title = 'Laporan';
    $breadcrumb .= ' <i class="fas fa-chevron-right"></i> <span>Laporan</span>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - E-Surat PTUN Banjarmasin</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../../assets/images/favicon.ico">
    
    <!-- External Libraries -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
    
    <!-- Meta Tags -->
    <meta name="description" content="Sistem E-Surat PTUN Banjarmasin - Pengelolaan Surat Masuk dan Surat Keluar">
    <meta name="keywords" content="e-surat, PTUN, Banjarmasin, surat masuk, surat keluar">
    <meta name="author" content="PTUN Banjarmasin">
    
    <!-- Enhanced CSS -->
    <style>
        /* Enhanced animations and transitions */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #3b82f6;
            --accent-color: #fbbf24;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-color: #f9fafb;
            --white-color: #ffffff;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--dark-color);
            line-height: 1.6;
        }

        /* Enhanced Layout Container */
        .main-container {
            display: flex;
            min-height: 100vh;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Enhanced Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary-color) 0%, #1e40af 100%);
            color: white;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            transform: translateX(0);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            overflow-y: auto;
            box-shadow: var(--shadow-xl);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar.collapsed {
            transform: translateX(-280px);
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            background: rgba(0, 0, 0, 0.15);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .sidebar-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.05), transparent);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%); }
            50% { transform: translateX(100%); }
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
        }

        .logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--white-color) 0%, #f1f5f9 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
            animation: float 3s ease-in-out infinite;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .sidebar-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
            background: linear-gradient(135deg, #ffffff 0%, #e2e8f0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sidebar-subtitle {
            font-size: 0.95rem;
            opacity: 0.9;
            margin: 0;
            position: relative;
            z-index: 2;
        }

        .sidebar-nav {
            padding: 1.5rem 0;
        }

        .nav-section {
            margin-bottom: 2rem;
        }

        .nav-section-title {
            padding: 0.75rem 1.5rem;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            opacity: 0.7;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 0.75rem;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.05), transparent);
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 3px solid transparent;
            position: relative;
            overflow: hidden;
            font-weight: 500;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            border-left-color: var(--accent-color);
            color: white;
            transform: translateX(8px);
            box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.1);
        }

        .nav-icon {
            margin-right: 1rem;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .nav-link:hover .nav-icon {
            transform: scale(1.1);
        }

        /* Enhanced Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--light-color);
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        /* Enhanced Header */
        .header {
            background: linear-gradient(135deg, var(--white-color) 0%, #f8fafc 100%);
            padding: 1rem 2rem;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: var(--shadow-lg);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .sidebar-toggle {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            font-size: 1.2rem;
            color: white;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 0.75rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow);
        }

        .sidebar-toggle:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #6b7280;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .breadcrumb a {
            color: var(--secondary-color);
            text-decoration: none;
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .breadcrumb a:hover {
            color: var(--primary-color);
        }

        .breadcrumb i.fa-chevron-right {
            font-size: 0.8rem;
            opacity: 0.6;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .current-time {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #6b7280;
            padding: 0.5rem 1rem;
            background: white;
            border-radius: 0.75rem;
            box-shadow: var(--shadow);
            border: 1px solid #e5e7eb;
        }

        .user-menu {
            position: relative;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, white 0%, #f8fafc 100%);
            border: 1px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow);
        }

        .user-info:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
        }

        .user-details {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--dark-color);
        }

        .user-role {
            font-size: 0.8rem;
            color: #6b7280;
            text-transform: capitalize;
        }

        /* Content Area */
        .content {
            padding: 2rem;
            animation: slideUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--dark-color) 0%, var(--primary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-description {
            color: #6b7280;
            font-size: 1.1rem;
            font-weight: 400;
        }

        /* Alert Enhancements */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
            animation: slideInDown 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
        }

        .alert-success {
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
            color: #047857;
            border-left-color: var(--success-color);
        }

        .alert-danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fef2f2 100%);
            color: #dc2626;
            border-left-color: var(--danger-color);
        }

        .alert-warning {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            color: #d97706;
            border-left-color: var(--warning-color);
        }

        .alert-info {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #1d4ed8;
            border-left-color: var(--secondary-color);
        }

        /* Enhanced Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            text-align: center;
            justify-content: center;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(30, 58, 138, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(107, 114, 128, 0.4);
        }

        /* Enhanced Cards */
        .card {
            background: linear-gradient(135deg, var(--white-color) 0%, #f8fafc 100%);
            border-radius: 1rem;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e5e7eb;
            backdrop-filter: blur(10px);
        }

        .card:hover {
            box-shadow: var(--shadow-xl);
            transform: translateY(-5px);
            border-color: var(--primary-color);
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e5e7eb;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .card-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-body {
            padding: 2rem;
        }

        /* Responsive Enhancements */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-280px);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .content {
                padding: 1rem;
            }
            
            .header-content {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
            
            .header-right {
                justify-content: space-between;
            }
            
            .page-title {
                font-size: 1.75rem;
            }
            
            .current-time {
                font-size: 0.8rem;
                padding: 0.5rem 0.75rem;
            }
        }

        /* Loading States */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Notification enhancements */
        .notification-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 5000;
            max-width: 400px;
        }

        .notification {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow-xl);
            margin-bottom: 1rem;
            overflow: hidden;
            transform: translateX(100%);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid var(--primary-color);
            backdrop-filter: blur(10px);
        }

        .notification.show {
            transform: translateX(0);
        }

        /* Enhanced scrollbar */
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
            transition: background 0.3s ease;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
        }

        /* Print optimizations */
        @media print {
            .sidebar, .header, .btn, .notification-container {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            .content {
                padding: 0 !important;
            }
            
            body {
                background: white !important;
            }
        }
    </style>
</head>
<body class="<?= $current_page ?>-page">
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.95); display: none; align-items: center; justify-content: center; z-index: 9999; backdrop-filter: blur(5px);">
        <div style="text-align: center;">
            <div class="loading-spinner" style="width: 60px; height: 60px; border: 4px solid #f3f3f3; border-top: 4px solid var(--primary-color); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem;"></div>
            <p style="color: var(--dark-color); font-weight: 600;">Memuat halaman...</p>
        </div>
    </div>
    
    <!-- Notification Container -->
    <div class="notification-container" id="notificationContainer"></div>

    <!-- Main Container -->
    <div class="main-container">
        
        <!-- Enhanced Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <div class="logo">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                </div>
                <div class="sidebar-title">E-Surat PTUN</div>
                <div class="sidebar-subtitle">Banjarmasin</div>
            </div>

            <div class="sidebar-nav">
                <!-- Main Navigation -->
                <div class="nav-section">
                    <div class="nav-section-title">Menu Utama</div>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="../../index.php" class="nav-link <?= $current_page == 'index' && $current_dir == '' ? 'active' : '' ?>">
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
                            <a href="../../pages/surat-masuk/index.php" class="nav-link <?= $current_dir == 'surat-masuk' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-inbox"></i>
                                Surat Masuk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../pages/surat-keluar/index.php" class="nav-link <?= $current_dir == 'surat-keluar' ? 'active' : '' ?>">
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
                            <a href="../../pages/reports/report-surat-masuk.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'report-surat-masuk') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-chart-line"></i>
                                Laporan Surat Masuk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../pages/reports/report-surat-keluar.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'report-surat-keluar') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                Laporan Surat Keluar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../pages/reports/report-bulanan.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'report-bulanan') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                Laporan Bulanan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../pages/reports/report-tahunan.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'report-tahunan') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-calendar"></i>
                                Laporan Tahunan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../pages/reports/report-rekap.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'report-rekap') !== false ? 'active' : '' ?>">
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
                            <a href="../../pages/users/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-users"></i>
                                Manajemen User
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../pages/settings/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'settings') !== false ? 'active' : '' ?>">
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
                            <a href="../../pages/profile/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'profile') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-user"></i>
                                Profil Saya
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../logout.php" class="nav-link" onclick="return confirm('Yakin ingin keluar dari sistem?')">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                Keluar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer" style="padding: 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.1); margin-top: auto; background: rgba(0, 0, 0, 0.1);">
                <div class="user-info-sidebar" style="text-align: center; color: rgba(255, 255, 255, 0.9);">
                    <div class="user-avatar-small" style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; margin: 0 auto 0.75rem; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem; border: 2px solid rgba(255, 255, 255, 0.3);">
                        <?= strtoupper(substr($current_user['nama_lengkap'], 0, 1)) ?>
                    </div>
                    <div class="user-name" style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.25rem;">
                        <?= escape(explode(' ', $current_user['nama_lengkap'])[0]) ?>
                    </div>
                    <div class="user-role" style="font-size: 0.75rem; opacity: 0.8; text-transform: capitalize;">
                        <?= ucfirst($current_user['level']) ?>
                    </div>
                    <div class="online-status" style="margin-top: 0.5rem; font-size: 0.7rem; opacity: 0.7; display: flex; align-items: center; justify-content: center; gap: 0.25rem;">
                        <div style="width: 6px; height: 6px; background: #10b981; border-radius: 50%; animation: pulse 2s infinite;"></div>
                        Online
                    </div>
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
                            <?= $breadcrumb ?>
                        </div>
                    </div>
                    <div class="header-right">
                        <div class="current-time">
                            <i class="fas fa-clock"></i>
                            <span class="current-datetime"></span>
                        </div>
                        <div class="user-menu">
                            <div class="user-info" onclick="toggleUserMenu()">
                                <div class="user-avatar">
                                    <?= strtoupper(substr($current_user['nama_lengkap'], 0, 1)) ?>
                                </div>
                                <div class="user-details">
                                    <span class="user-name"><?= escape($current_user['nama_lengkap']) ?></span>
                                    <span class="user-role"><?= escape($current_user['jabatan']) ?></span>
                                </div>
                                <i class="fas fa-chevron-down" style="transition: transform 0.3s ease;"></i>
                            </div>
                            
                            <!-- Dropdown Menu -->
                            <div class="user-dropdown" id="userDropdown" style="position: absolute; top: 100%; right: 0; background: white; border-radius: 0.75rem; box-shadow: var(--shadow-xl); min-width: 220px; opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); z-index: 1000; border: 1px solid #e5e7eb; overflow: hidden;">
                                <div style="padding: 1rem; border-bottom: 1px solid #e5e7eb; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                                            <?= strtoupper(substr($current_user['nama_lengkap'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: var(--dark-color);"><?= escape($current_user['nama_lengkap']) ?></div>
                                            <div style="font-size: 0.8rem; color: #6b7280;"><?= escape($current_user['jabatan']) ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div style="padding: 0.5rem 0;">
                                    <a href="../../pages/profile/index.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--dark-color); text-decoration: none; transition: background 0.3s ease;">
                                        <i class="fas fa-user" style="color: var(--primary-color);"></i>
                                        Profil Saya
                                    </a>
                                    <a href="../../pages/settings/index.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--dark-color); text-decoration: none; transition: background 0.3s ease;">
                                        <i class="fas fa-cog" style="color: #6b7280;"></i>
                                        Pengaturan
                                    </a>
                                    <div style="height: 1px; background: #e5e7eb; margin: 0.5rem 0;"></div>
                                    <a href="../../logout.php" onclick="return confirm('Yakin ingin keluar dari sistem?')" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--danger-color); text-decoration: none; transition: background 0.3s ease;">
                                        <i class="fas fa-sign-out-alt"></i>
                                        Keluar Sistem
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-header-content" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                        <div class="header-left">
                            <h1 class="page-title">
                                <?php
                                switch ($current_dir) {
                                    case 'surat-masuk':
                                        echo '<i class="fas fa-inbox"></i> ';
                                        if ($current_page == 'tambah') {
                                            echo 'Tambah Surat Masuk';
                                        } elseif ($current_page == 'edit') {
                                            echo 'Edit Surat Masuk';
                                        } elseif ($current_page == 'detail') {
                                            echo 'Detail Surat Masuk';
                                        } else {
                                            echo 'Surat Masuk';
                                        }
                                        break;
                                    case 'surat-keluar':
                                        echo '<i class="fas fa-paper-plane"></i> ';
                                        if ($current_page == 'tambah') {
                                            echo 'Tambah Surat Keluar';
                                        } else {
                                            echo 'Surat Keluar';
                                        }
                                        break;
                                    case 'reports':
                                        echo '<i class="fas fa-chart-line"></i> Laporan';
                                        break;
                                    default:
                                        echo '<i class="fas fa-tachometer-alt"></i> Dashboard';
                                }
                                ?>
                            </h1>
                            <p class="page-description">
                                <?php
                                switch ($current_dir) {
                                    case 'surat-masuk':
                                        if ($current_page == 'tambah') {
                                            echo 'Input data surat masuk baru ke dalam sistem';
                                        } elseif ($current_page == 'edit') {
                                            echo 'Ubah data surat masuk yang sudah ada';
                                        } elseif ($current_page == 'detail') {
                                            echo 'Informasi lengkap surat masuk';
                                        } else {
                                            echo 'Kelola dan pantau semua surat masuk yang diterima';
                                        }
                                        break;
                                    case 'surat-keluar':
                                        if ($current_page == 'tambah') {
                                            echo 'Buat surat keluar baru untuk dikirim';
                                        } else {
                                            echo 'Kelola dan pantau semua surat keluar yang dikirim';
                                        }
                                        break;
                                    case 'reports':
                                        echo 'Laporan dan analisis data surat masuk dan keluar';
                                        break;
                                    default:
                                        echo 'Ringkasan sistem dan aktivitas terbaru E-Surat PTUN Banjarmasin';
                                }
                                ?>
                            </p>
                        </div>
                        
                        <div class="page-header-actions" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                            <?php if ($current_dir == 'surat-masuk' && $current_page == 'index'): ?>
                            <a href="tambah.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Tambah Surat Masuk
                            </a>
                            <?php elseif ($current_dir == 'surat-keluar' && $current_page == 'index'): ?>
                            <a href="tambah.php" class="btn btn-success">
                                <i class="fas fa-plus"></i>
                                Buat Surat Keluar
                            </a>
                            <?php elseif ($current_page == 'index' && $current_dir == ''): ?>
                            <button class="btn btn-primary" onclick="refreshDashboard()">
                                <i class="fas fa-sync-alt"></i>
                                Refresh
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                        <i class="fas fa-check-circle" style="color: var(--success-color); font-size: 1.2rem; margin-top: 0.1rem;"></i>
                        <div>
                            <strong>Berhasil!</strong>
                            <div><?= $_SESSION['success'] ?></div>
                        </div>
                        <button type="button" onclick="this.parentElement.parentElement.remove()" style="margin-left: auto; background: none; border: none; color: inherit; opacity: 0.7; cursor: pointer; font-size: 1.2rem;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <?php unset($_SESSION['success']); endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                        <i class="fas fa-exclamation-circle" style="color: var(--danger-color); font-size: 1.2rem; margin-top: 0.1rem;"></i>
                        <div>
                            <strong>Error!</strong>
                            <div><?= $_SESSION['error'] ?></div>
                        </div>
                        <button type="button" onclick="this.parentElement.parentElement.remove()" style="margin-left: auto; background: none; border: none; color: inherit; opacity: 0.7; cursor: pointer; font-size: 1.2rem;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <?php unset($_SESSION['error']); endif; ?>

                <!-- Main Content Area -->