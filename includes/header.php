<?php
// includes/header.php - Enhanced version
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

// Get notifications for PTUN
try {
    // Get pending surat masuk count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM surat_masuk WHERE status = 'Masuk'");
    $pending_masuk = $stmt->fetch()['count'];
    
    // Get surat masuk this month
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM surat_masuk WHERE MONTH(tanggal_diterima) = MONTH(CURRENT_DATE()) AND YEAR(tanggal_diterima) = YEAR(CURRENT_DATE())");
    $masuk_bulan_ini = $stmt->fetch()['count'];
    
    // Get draft surat keluar
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM surat_keluar WHERE status = 'Draft'");
    $draft_keluar = $stmt->fetch()['count'];
    
    // Recent activities
    $stmt = $pdo->query("
        (SELECT 'masuk' as type, nomor_surat, pengirim as instansi, perihal, created_at 
         FROM surat_masuk ORDER BY created_at DESC LIMIT 3)
        UNION ALL
        (SELECT 'keluar' as type, nomor_surat, penerima as instansi, perihal, created_at 
         FROM surat_keluar ORDER BY created_at DESC LIMIT 3)
        ORDER BY created_at DESC LIMIT 5
    ");
    $recent_activities = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $pending_masuk = 0;
    $masuk_bulan_ini = 0;
    $draft_keluar = 0;
    $recent_activities = [];
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--dark-color);
            line-height: 1.6;
        }

        /* Main Container */
        .main-container {
            display: flex;
            min-height: 100vh;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Enhanced Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            color: white;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            transform: translateX(0);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
            background: rgba(0, 0, 0, 0.3);
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
            animation: shimmer 4s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%); }
            50% { transform: translateX(100%); }
        }

        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
        }

        .logo {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--accent-color) 0%, #f59e0b 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            color: var(--dark-color);
            animation: logoFloat 3s ease-in-out infinite;
            box-shadow: 0 12px 28px rgba(251, 191, 36, 0.4);
            margin-bottom: 1rem;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            25% { transform: translateY(-8px) rotate(1deg); }
            50% { transform: translateY(-15px) rotate(0deg); }
            75% { transform: translateY(-8px) rotate(-1deg); }
        }

        .sidebar-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0 0 0.25rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 2;
            background: linear-gradient(135deg, #ffffff 0%, var(--accent-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sidebar-subtitle {
            font-size: 0.9rem;
            opacity: 0.85;
            margin: 0;
            position: relative;
            z-index: 2;
            font-weight: 500;
        }

        .sidebar-nav {
            padding: 1.5rem 0;
        }

        .nav-section {
            margin-bottom: 2rem;
        }

        .nav-section-title {
            padding: 0.75rem 1.5rem;
            font-size: 0.75rem;
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

        .nav-item {
            position: relative;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 3px solid transparent;
            position: relative;
            overflow: hidden;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(251, 191, 36, 0.2), transparent);
            transition: left 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-link:hover::before,
        .nav-link.active::before {
            left: 100%;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(251, 191, 36, 0.15);
            border-left-color: var(--accent-color);
            color: white;
            transform: translateX(10px);
            box-shadow: inset 0 0 20px rgba(251, 191, 36, 0.1);
        }

        .nav-link.active {
            background: rgba(251, 191, 36, 0.2);
            box-shadow: inset 0 0 20px rgba(251, 191, 36, 0.2);
        }

        .nav-icon {
            margin-right: 1rem;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .nav-link:hover .nav-icon,
        .nav-link.active .nav-icon {
            transform: scale(1.2);
            color: var(--accent-color);
        }

        /* Main Content */
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
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 2rem;
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
            transform: translateY(-3px) rotate(180deg);
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
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
        }

        .breadcrumb a:hover {
            color: var(--primary-color);
            background: rgba(59, 130, 246, 0.1);
        }

        /* PTUN Header Info */
        .ptun-info {
            text-align: center;
            flex: 1;
        }

        .ptun-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .ptun-subtitle {
            font-size: 0.85rem;
            color: #6b7280;
            margin: 0;
            font-weight: 500;
        }

        .current-time {
            font-size: 0.8rem;
            color: #9ca3af;
            margin-top: 0.25rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Notification System */
        .notifications-container {
            position: relative;
        }

        .notification-bell {
            background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
            border: none;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
            font-size: 1.2rem;
        }

        .notification-bell:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: var(--shadow-lg);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow-xl);
            width: 380px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .notification-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .notification-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .notification-title {
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .notification-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .notification-item:hover {
            background: #f8fafc;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-content {
            display: flex;
            gap: 1rem;
        }

        .notification-icon {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .notification-icon.masuk {
            background: rgba(59, 130, 246, 0.1);
            color: var(--secondary-color);
        }

        .notification-icon.keluar {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .notification-icon.urgent {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        .notification-text {
            flex: 1;
        }

        .notification-message {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
            line-height: 1.4;
        }

        .notification-meta {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .notification-time {
            font-size: 0.75rem;
            color: #9ca3af;
            margin-left: auto;
            flex-shrink: 0;
        }

        /* User Menu */
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

        .user-chevron {
            transition: transform 0.3s ease;
            color: #9ca3af;
        }

        .user-info.open .user-chevron {
            transform: rotate(180deg);
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 0.75rem;
            box-shadow: var(--shadow-xl);
            min-width: 220px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .user-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .user-dropdown-header {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .user-dropdown-content {
            padding: 0.5rem 0;
        }

        .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--dark-color);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .user-dropdown a:hover {
            background: #f8fafc;
            color: var(--primary-color);
        }

        .user-dropdown a i {
            width: 16px;
            text-align: center;
        }

        .dropdown-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 0.5rem 0;
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

        /* Mobile Responsiveness */
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
            
            .header-content {
                grid-template-columns: auto 1fr;
                gap: 1rem;
            }
            
            .ptun-info {
                display: none;
            }
            
            .content {
                padding: 1rem;
            }
            
            .notification-dropdown {
                width: 320px;
                right: -50px;
            }
        }

        @media (max-width: 480px) {
            .header-right {
                gap: 0.5rem;
            }
            
            .user-details {
                display: none;
            }
            
            .notification-dropdown {
                width: 280px;
                right: -100px;
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

        /* Enhanced scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
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

    <!-- Main Container -->
    <div class="main-container">
        
        <!-- Enhanced Sidebar -->
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
                            <a href="../../index.php" class="nav-link <?= $current_page == 'index' && $current_dir == '' ? 'active' : '' ?>" data-page="dashboard">
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
                            <a href="../../pages/surat-masuk/index.php" class="nav-link <?= $current_dir == 'surat-masuk' ? 'active' : '' ?>" data-page="surat-masuk">
                                <i class="nav-icon fas fa-inbox"></i>
                                Surat Masuk
                                <?php if ($pending_masuk > 0): ?>
                                <span class="nav-badge" style="margin-left: auto; background: var(--danger-color); color: white; font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 1rem; font-weight: 600;"><?= $pending_masuk ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../pages/surat-keluar/index.php" class="nav-link <?= $current_dir == 'surat-keluar' ? 'active' : '' ?>" data-page="surat-keluar">
                                <i class="nav-icon fas fa-paper-plane"></i>
                                Surat Keluar
                                <?php if ($draft_keluar > 0): ?>
                                <span class="nav-badge" style="margin-left: auto; background: var(--warning-color); color: white; font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 1rem; font-weight: 600;"><?= $draft_keluar ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Reports -->
                <div class="nav-section">
                    <div class="nav-section-title">Laporan & Analisis</div>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="../../pages/reports/report-surat-masuk.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'report-surat-masuk') !== false ? 'active' : '' ?>" data-page="report-masuk">
                                <i class="nav-icon fas fa-chart-line"></i>
                                Laporan Surat Masuk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../pages/reports/report-surat-keluar.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'report-surat-keluar') !== false ? 'active' : '' ?>" data-page="report-keluar">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                Laporan Surat Keluar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../pages/reports/report-bulanan.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'report-bulanan') !== false ? 'active' : '' ?>" data-page="report-bulanan">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                Laporan Bulanan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../pages/reports/report-tahunan.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'report-tahunan') !== false ? 'active' : '' ?>" data-page="report-tahunan">
                                <i class="nav-icon fas fa-calendar"></i>
                                Laporan Tahunan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../pages/reports/report-rekap.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'report-rekap') !== false ? 'active' : '' ?>" data-page="report-rekap">
                                <i class="nav-icon fas fa-file-alt"></i>
                                Rekapitulasi
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Settings (Admin Only) -->
                <?php if ($current_user['level'] == 'admin'): ?>
                <div class="nav-section">
                    <div class="nav-section-title">Administrasi</div>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="../../pages/users/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'active' : '' ?>" data-page="users">
                                <i class="nav-icon fas fa-users"></i>
                                Manajemen User
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../pages/settings/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'settings') !== false ? 'active' : '' ?>" data-page="settings">
                                <i class="nav-icon fas fa-cog"></i>
                                Pengaturan Sistem
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
                            <a href="../../pages/profile/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'profile') !== false ? 'active' : '' ?>" data-page="profile">
                                <i class="nav-icon fas fa-user"></i>
                                Profil Saya
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../../logout.php" class="nav-link" onclick="return confirmLogout()" data-page="logout">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                Keluar Sistem
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer" style="padding: 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.1); margin-top: auto; background: rgba(0, 0, 0, 0.2);">
                <div class="user-info-sidebar" style="text-align: center; color: rgba(255, 255, 255, 0.9);">
                    <div class="user-avatar-small" style="width: 50px; height: 50px; background: rgba(251, 191, 36, 0.3); border-radius: 50%; margin: 0 auto 0.75rem; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem; border: 2px solid rgba(251, 191, 36, 0.5);">
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
                    
                    <!-- PTUN Info Center -->
                    <div class="ptun-info">
                        <div class="ptun-title">Pengadilan Tata Usaha Negara Banjarmasin</div>
                        <div class="ptun-subtitle">Sistem Elektronik Surat Menyurat</div>
                        <div class="current-time">
                            <i class="fas fa-clock"></i>
                            <span class="current-datetime"></span>
                        </div>
                    </div>
                    
                    <div class="header-right">
                        <!-- Notifications -->
                        <div class="notifications-container">
                            <button type="button" class="notification-bell" id="notificationToggle" title="Notifikasi">
                                <i class="fas fa-bell"></i>
                                <?php if ($pending_masuk + $draft_keluar > 0): ?>
                                <span class="notification-badge"><?= $pending_masuk + $draft_keluar ?></span>
                                <?php endif; ?>
                            </button>
                            
                            <div class="notification-dropdown" id="notificationDropdown">
                                <div class="notification-header">
                                    <h4 class="notification-title">
                                        <i class="fas fa-bell"></i>
                                        Notifikasi PTUN
                                    </h4>
                                </div>
                                
                                <div class="notification-list">
                                    <?php if ($pending_masuk > 0): ?>
                                    <div class="notification-item" onclick="navigateToPage('../../pages/surat-masuk/index.php')">
                                        <div class="notification-content">
                                            <div class="notification-icon urgent">
                                                <i class="fas fa-exclamation-circle"></i>
                                            </div>
                                            <div class="notification-text">
                                                <div class="notification-message">
                                                    <?= $pending_masuk ?> Surat Masuk Perlu Disposisi
                                                </div>
                                                <div class="notification-meta">
                                                    Segera berikan disposisi untuk surat yang masuk
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($draft_keluar > 0): ?>
                                    <div class="notification-item" onclick="navigateToPage('../../pages/surat-keluar/index.php')">
                                        <div class="notification-content">
                                            <div class="notification-icon keluar">
                                                <i class="fas fa-edit"></i>
                                            </div>
                                            <div class="notification-text">
                                                <div class="notification-message">
                                                    <?= $draft_keluar ?> Draft Surat Keluar
                                                </div>
                                                <div class="notification-meta">
                                                    Draft surat siap untuk dikirim
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($masuk_bulan_ini > 0): ?>
                                    <div class="notification-item">
                                        <div class="notification-content">
                                            <div class="notification-icon masuk">
                                                <i class="fas fa-calendar-check"></i>
                                            </div>
                                            <div class="notification-text">
                                                <div class="notification-message">
                                                    <?= $masuk_bulan_ini ?> Surat Masuk Bulan Ini
                                                </div>
                                                <div class="notification-meta">
                                                    Aktivitas surat masuk periode <?= date('F Y') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Recent Activities -->
                                    <?php foreach (array_slice($recent_activities, 0, 3) as $activity): ?>
                                    <div class="notification-item">
                                        <div class="notification-content">
                                            <div class="notification-icon <?= $activity['type'] ?>">
                                                <i class="fas fa-<?= $activity['type'] == 'masuk' ? 'inbox' : 'paper-plane' ?>"></i>
                                            </div>
                                            <div class="notification-text">
                                                <div class="notification-message">
                                                    <?= $activity['type'] == 'masuk' ? 'Surat Masuk' : 'Surat Keluar' ?>: <?= escape($activity['nomor_surat']) ?>
                                                </div>
                                                <div class="notification-meta">
                                                    <?= $activity['type'] == 'masuk' ? 'Dari' : 'Kepada' ?>: <?= escape($activity['instansi']) ?>
                                                </div>
                                            </div>
                                            <div class="notification-time">
                                                <?= timeAgo($activity['created_at']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($recent_activities) && $pending_masuk == 0 && $draft_keluar == 0): ?>
                                    <div class="notification-item" style="text-align: center; padding: 2rem;">
                                        <div style="color: #9ca3af; font-size: 0.9rem;">
                                            <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                            Tidak ada notifikasi baru
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="user-menu">
                            <div class="user-info" id="userMenuToggle">
                                <div class="user-avatar">
                                    <?= strtoupper(substr($current_user['nama_lengkap'], 0, 1)) ?>
                                </div>
                                <div class="user-details">
                                    <span class="user-name"><?= escape($current_user['nama_lengkap']) ?></span>
                                    <span class="user-role"><?= escape($current_user['jabatan']) ?></span>
                                </div>
                                <i class="fas fa-chevron-down user-chevron"></i>
                            </div>
                            
                            <!-- User Dropdown -->
                            <div class="user-dropdown" id="userDropdown">
                                <div class="user-dropdown-header">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                                            <?= strtoupper(substr($current_user['nama_lengkap'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: var(--dark-color); font-size: 0.95rem;"><?= escape($current_user['nama_lengkap']) ?></div>
                                            <div style="font-size: 0.8rem; color: #6b7280;"><?= escape($current_user['jabatan']) ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="user-dropdown-content">
                                    <a href="../../pages/profile/index.php">
                                        <i class="fas fa-user"></i>
                                        Profil Saya
                                    </a>
                                    <a href="../../pages/settings/index.php">
                                        <i class="fas fa-cog"></i>
                                        Pengaturan
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a href="../../logout.php" onclick="return confirmLogout()">
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

<script>
// Enhanced Sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    initEnhancedSidebar();
    initNotifications();
    initUserMenu();
    updateDateTime();
    setInterval(updateDateTime, 1000);
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
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Only prevent default if not going to actual page
            const href = this.getAttribute('href');
            if (href && !href.startsWith('#')) {
                // Let the link navigate normally
                return;
            }
            
            e.preventDefault();
            
            // Remove active from all links
            navLinks.forEach(l => l.classList.remove('active'));
            
            // Add active to clicked link
            this.classList.add('active');
            
            // Add ripple effect
            const ripple = document.createElement('span');
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(251, 191, 36, 0.6);
                transform: scale(0);
                animation: ripple 600ms linear;
                width: 100px;
                height: 100px;
                left: ${e.offsetX - 50}px;
                top: ${e.offsetY - 50}px;
            `;
            
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
        
        // Enhanced hover effects
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

function initNotifications() {
    const notificationToggle = document.getElementById('notificationToggle');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    if (notificationToggle) {
        notificationToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
        });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!notificationDropdown.contains(e.target)) {
            notificationDropdown.classList.remove('show');
        }
    });
}

function initUserMenu() {
    const userMenuToggle = document.getElementById('userMenuToggle');
    const userDropdown = document.getElementById('userDropdown');
    const userInfo = document.querySelector('.user-info');
    
    if (userMenuToggle) {
        userMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
            userInfo.classList.toggle('open');
        });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!userDropdown.contains(e.target) && !userMenuToggle.contains(e.target)) {
            userDropdown.classList.remove('show');
            userInfo.classList.remove('open');
        }
    });
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

function navigateToPage(url) {
    window.location.href = url;
}

function confirmLogout() {
    return confirm('Yakin ingin keluar dari sistem E-Surat PTUN Banjarmasin?\n\nAnda akan dialihkan ke halaman login.');
}

function refreshDashboard() {
    showLoading('Memperbarui dashboard...');
    setTimeout(() => {
        location.reload();
    }, 1000);
}

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

// Add ripple effect CSS
const rippleStyles = document.createElement('style');
rippleStyles.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .nav-link {
        position: relative;
        overflow: hidden;
    }
    
    /* Enhanced animations */
    @keyframes fadeInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes fadeInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .nav-section {
        animation: fadeInLeft 0.6s ease-out;
    }
    
    .nav-section:nth-child(even) {
        animation: fadeInRight 0.6s ease-out;
    }
    
    .notification-item {
        transition: all 0.3s ease;
    }
    
    .notification-item:hover {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        transform: translateX(5px);
    }
    
    /* Enhanced button effects */
    .btn {
        position: relative;
        overflow: hidden;
    }
    
    .btn::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.3s, height 0.3s;
    }
    
    .btn:active::after {
        width: 300px;
        height: 300px;
    }
    
    /* Page transitions */
    .content {
        animation: slideInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Enhanced form styles */
    .form-control:focus {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
    }
    
    /* Enhanced alert styles */
    .alert {
        animation: slideInDown 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
`;

document.head.appendChild(rippleStyles);

// Page loading effects
window.addEventListener('beforeunload', function() {
    showLoading('Memuat halaman...');
});

// Enhanced page interactions
document.addEventListener('click', function(e) {
    // Add click effect to buttons
    if (e.target.matches('.btn') || e.target.closest('.btn')) {
        const btn = e.target.matches('.btn') ? e.target : e.target.closest('.btn');
        btn.style.transform = 'scale(0.95)';
        setTimeout(() => {
            btn.style.transform = '';
        }, 150);
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Alt + S untuk toggle sidebar
    if (e.altKey && e.key === 's') {
        e.preventDefault();
        document.getElementById('sidebarToggle').click();
    }
    
    // Alt + N untuk notifikasi
    if (e.altKey && e.key === 'n') {
        e.preventDefault();
        document.getElementById('notificationToggle').click();
    }
    
    // Escape untuk menutup dropdown
    if (e.key === 'Escape') {
        document.getElementById('notificationDropdown').classList.remove('show');
        document.getElementById('userDropdown').classList.remove('show');
        document.querySelector('.user-info').classList.remove('open');
    }
});

// Auto-refresh notifications every 5 minutes
setInterval(() => {
    if (document.visibilityState === 'visible') {
        // You can add AJAX call here to refresh notification count
        console.log('Auto-refreshing notifications...');
    }
}, 300000);

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
</script>

<?php
// Helper function for time ago
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Baru saja';
    if ($time < 3600) return floor($time/60) . ' menit lalu';
    if ($time < 86400) return floor($time/3600) . ' jam lalu';
    if ($time < 2592000) return floor($time/86400) . ' hari lalu';
    if ($time < 31104000) return floor($time/2592000) . ' bulan lalu';
    
    return floor($time/31104000) . ' tahun lalu';
}

// Page title and description styles
echo '<style>
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
    animation: fadeInUp 0.8s ease;
}

.page-description {
    color: #6b7280;
    font-size: 1.1rem;
    font-weight: 400;
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

/* Enhanced alert styles */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
    border-left: 4px solid;
    backdrop-filter: blur(10px);
    box-shadow: var(--shadow);
    position: relative;
    overflow: hidden;
}

.alert::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
    animation: shimmer 2s infinite;
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

/* Print styles */
@media print {
    .sidebar, 
    .header, 
    .btn, 
    .notification-dropdown,
    .user-dropdown,
    .loading-overlay {
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
    
    .page-title {
        color: #000 !important;
        -webkit-text-fill-color: #000 !important;
    }
}
</style>';
?>