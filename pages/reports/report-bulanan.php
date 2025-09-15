<?php
// pages/reports/report-bulanan.php - Monthly Report
$page_title = "Laporan Bulanan";
$page_description = "Laporan detail surat masuk dan keluar per bulan";
$breadcrumbs = [
    ['title' => 'Laporan', 'url' => '#'],
    ['title' => 'Laporan Bulanan']
];

require_once '../../includes/header.php';

// Get filter parameters
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Validate month and year
$bulan = max(1, min(12, (int)$bulan));
$tahun = max(2020, min(date('Y') + 1, (int)$tahun));

$bulan_nama = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

try {
    // Daily statistics for the selected month
    $daily_stats = [];
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
    
    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);
        
        // Get surat masuk count for this day
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM surat_masuk WHERE DATE(tanggal_diterima) = ?");
        $stmt->execute([$date]);
        $masuk_count = $stmt->fetch()['count'];
        
        // Get surat keluar count for this day
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM surat_keluar WHERE DATE(tanggal_keluar) = ?");
        $stmt->execute([$date]);
        $keluar_count = $stmt->fetch()['count'];
        
        $daily_stats[] = [
            'date' => $date,
            'day' => $day,
            'day_name' => date('l', strtotime($date)),
            'masuk' => $masuk_count,
            'keluar' => $keluar_count
        ];
    }
    
    // Monthly summary statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_masuk,
            SUM(CASE WHEN status = 'Masuk' THEN 1 ELSE 0 END) as masuk_pending,
            SUM(CASE WHEN status = 'Diproses' THEN 1 ELSE 0 END) as masuk_diproses,
            SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as masuk_selesai,
            SUM(CASE WHEN sifat_surat = 'Biasa' THEN 1 ELSE 0 END) as masuk_biasa,
            SUM(CASE WHEN sifat_surat = 'Penting' THEN 1 ELSE 0 END) as masuk_penting,
            SUM(CASE WHEN sifat_surat = 'Segera' THEN 1 ELSE 0 END) as masuk_segera,
            SUM(CASE WHEN sifat_surat = 'Sangat Segera' THEN 1 ELSE 0 END) as masuk_sangat_segera
        FROM surat_masuk 
        WHERE MONTH(tanggal_diterima) = ? AND YEAR(tanggal_diterima) = ?
    ");
    $stmt->execute([$bulan, $tahun]);
    $masuk_stats = $stmt->fetch();
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_keluar,
            SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END) as keluar_draft,
            SUM(CASE WHEN status = 'Dikirim' THEN 1 ELSE 0 END) as keluar_dikirim,
            SUM(CASE WHEN status = 'Sampai' THEN 1 ELSE 0 END) as keluar_sampai,
            SUM(CASE WHEN sifat_surat = 'Biasa' THEN 1 ELSE 0 END) as keluar_biasa,
            SUM(CASE WHEN sifat_surat = 'Penting' THEN 1 ELSE 0 END) as keluar_penting,
            SUM(CASE WHEN sifat_surat = 'Segera' THEN 1 ELSE 0 END) as keluar_segera,
            SUM(CASE WHEN sifat_surat = 'Sangat Segera' THEN 1 ELSE 0 END) as keluar_sangat_segera
        FROM surat_keluar 
        WHERE MONTH(tanggal_keluar) = ? AND YEAR(tanggal_keluar) = ?
    ");
    $stmt->execute([$bulan, $tahun]);
    $keluar_stats = $stmt->fetch();
    
    // Top senders and receivers
    $stmt = $pdo->prepare("
        SELECT pengirim, COUNT(*) as count 
        FROM surat_masuk 
        WHERE MONTH(tanggal_diterima) = ? AND YEAR(tanggal_diterima) = ?
        GROUP BY pengirim 
        ORDER BY count DESC 
        LIMIT 10
    ");
    $stmt->execute([$bulan, $tahun]);
    $top_senders = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("
        SELECT penerima, COUNT(*) as count 
        FROM surat_keluar 
        WHERE MONTH(tanggal_keluar) = ? AND YEAR(tanggal_keluar) = ?
        GROUP BY penerima 
        ORDER BY count DESC 
        LIMIT 10
    ");
    $stmt->execute([$bulan, $tahun]);
    $top_receivers = $stmt->fetchAll();
    
    // Weekly breakdown
    $weekly_stats = [];
    $first_day = strtotime("$tahun-$bulan-01");
    $last_day = strtotime("$tahun-$bulan-$days_in_month");
    
    // Find first Monday of the month or before
    $start_of_week = strtotime('last monday', $first_day);
    if (date('N', $first_day) == 1) {
        $start_of_week = $first_day;
    }
    
    $week_num = 1;
    $current_week_start = $start_of_week;
    
    while ($current_week_start <= $last_day) {
        $current_week_end = strtotime('+6 days', $current_week_start);
        
        // Adjust for month boundaries
        $week_start_in_month = max($current_week_start, $first_day);
        $week_end_in_month = min($current_week_end, $last_day);
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as masuk FROM surat_masuk 
            WHERE tanggal_diterima BETWEEN ? AND ?
        ");
        $stmt->execute([date('Y-m-d', $week_start_in_month), date('Y-m-d', $week_end_in_month)]);
        $week_masuk = $stmt->fetch()['masuk'];
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as keluar FROM surat_keluar 
            WHERE tanggal_keluar BETWEEN ? AND ?
        ");
        $stmt->execute([date('Y-m-d', $week_start_in_month), date('Y-m-d', $week_end_in_month)]);
        $week_keluar = $stmt->fetch()['keluar'];
        
        $weekly_stats[] = [
            'week' => $week_num,
            'start_date' => date('d M', $week_start_in_month),
            'end_date' => date('d M', $week_end_in_month),
            'masuk' => $week_masuk,
            'keluar' => $week_keluar
        ];
        
        $current_week_start = strtotime('+7 days', $current_week_start);
        $week_num++;
    }
    
    // Performance metrics
    $performance_metrics = [
        'avg_processing_time' => 0,
        'completion_rate' => $masuk_stats['total_masuk'] > 0 ? round(($masuk_stats['masuk_selesai'] / $masuk_stats['total_masuk']) * 100, 1) : 0,
        'response_rate' => $keluar_stats['total_keluar'] > 0 ? round(($keluar_stats['keluar_sampai'] / $keluar_stats['total_keluar']) * 100, 1) : 0,
        'priority_handling' => ($masuk_stats['masuk_segera'] + $masuk_stats['masuk_sangat_segera'] + $keluar_stats['keluar_segera'] + $keluar_stats['keluar_sangat_segera'])
    ];
    
    // Compare with previous month
    $prev_month = $bulan - 1;
    $prev_year = $tahun;
    if ($prev_month < 1) {
        $prev_month = 12;
        $prev_year--;
    }
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as prev_masuk FROM surat_masuk 
        WHERE MONTH(tanggal_diterima) = ? AND YEAR(tanggal_diterima) = ?
    ");
    $stmt->execute([$prev_month, $prev_year]);
    $prev_masuk = $stmt->fetch()['prev_masuk'];
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as prev_keluar FROM surat_keluar 
        WHERE MONTH(tanggal_keluar) = ? AND YEAR(tanggal_keluar) = ?
    ");
    $stmt->execute([$prev_month, $prev_year]);
    $prev_keluar = $stmt->fetch()['prev_keluar'];
    
    $comparison = [
        'masuk_change' => $prev_masuk > 0 ? round((($masuk_stats['total_masuk'] - $prev_masuk) / $prev_masuk) * 100, 1) : 0,
        'keluar_change' => $prev_keluar > 0 ? round((($keluar_stats['total_keluar'] - $prev_keluar) / $prev_keluar) * 100, 1) : 0,
        'prev_month_name' => $bulan_nama[$prev_month] . ' ' . $prev_year
    ];
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal memuat data laporan: " . $e->getMessage();
    $daily_stats = [];
    $masuk_stats = array_fill_keys(['total_masuk', 'masuk_pending', 'masuk_diproses', 'masuk_selesai', 'masuk_biasa', 'masuk_penting', 'masuk_segera', 'masuk_sangat_segera'], 0);
    $keluar_stats = array_fill_keys(['total_keluar', 'keluar_draft', 'keluar_dikirim', 'keluar_sampai', 'keluar_biasa', 'keluar_penting', 'keluar_segera', 'keluar_sangat_segera'], 0);
    $top_senders = [];
    $top_receivers = [];
    $weekly_stats = [];
    $performance_metrics = ['avg_processing_time' => 0, 'completion_rate' => 0, 'response_rate' => 0, 'priority_handling' => 0];
    $comparison = ['masuk_change' => 0, 'keluar_change' => 0, 'prev_month_name' => ''];
}
?>

<!-- Filter Section -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-calendar-alt"></i>
            Filter Laporan Bulanan
        </h3>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <div class="form-group">
                    <label class="form-label">Bulan</label>
                    <select name="bulan" class="form-control form-select">
                        <?php foreach ($bulan_nama as $num => $nama): ?>
                        <option value="<?= $num ?>" <?= $bulan == $num ? 'selected' : '' ?>><?= $nama ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tahun</label>
                    <select name="tahun" class="form-control form-select">
                        <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                        <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Tampilkan Laporan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Report Header -->
<div class="report-header" style="text-align: center; margin-bottom: 2rem; padding: 2rem; background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%); color: white; border-radius: 1rem;">
    <h1 style="margin: 0 0 0.5rem; font-size: 2rem; font-weight: 700;">
        Laporan Bulanan E-Surat
    </h1>
    <h2 style="margin: 0 0 1rem; font-size: 1.5rem; font-weight: 500;">
        Pengadilan Tata Usaha Negara Banjarmasin
    </h2>
    <p style="margin: 0; font-size: 1.2rem; opacity: 0.9;">
        Periode: <?= $bulan_nama[$bulan] ?> <?= $tahun ?>
    </p>
</div>

<!-- Summary Statistics -->
<div class="summary-section" style="margin-bottom: 2rem;">
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <div class="stat-value"><?= number_format($masuk_stats['total_masuk']) ?></div>
            <div class="stat-label">Total Surat Masuk</div>
            <?php if ($comparison['masuk_change'] != 0): ?>
            <div class="stat-change" style="font-size: 0.8rem; margin-top: 0.5rem; color: <?= $comparison['masuk_change'] > 0 ? '#10b981' : '#ef4444' ?>;">
                <i class="fas fa-arrow-<?= $comparison['masuk_change'] > 0 ? 'up' : 'down' ?>"></i>
                <?= abs($comparison['masuk_change']) ?>% dari bulan lalu
            </div>
            <?php endif; ?>
        </div>
        
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-paper-plane"></i>
            </div>
            <div class="stat-value"><?= number_format($keluar_stats['total_keluar']) ?></div>
            <div class="stat-label">Total Surat Keluar</div>
            <?php if ($comparison['keluar_change'] != 0): ?>
            <div class="stat-change" style="font-size: 0.8rem; margin-top: 0.5rem; color: <?= $comparison['keluar_change'] > 0 ? '#10b981' : '#ef4444' ?>;">
                <i class="fas fa-arrow-<?= $comparison['keluar_change'] > 0 ? 'up' : 'down' ?>"></i>
                <?= abs($comparison['keluar_change']) ?>% dari bulan lalu
            </div>
            <?php endif; ?>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-value"><?= $performance_metrics['completion_rate'] ?>%</div>
            <div class="stat-label">Tingkat Penyelesaian</div>
            <div class="stat-detail" style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                <?= $masuk_stats['masuk_selesai'] ?> dari <?= $masuk_stats['total_masuk'] ?> surat
            </div>
        </div>
        
        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-value"><?= number_format($performance_metrics['priority_handling']) ?></div>
            <div class="stat-label">Surat Prioritas</div>
            <div class="stat-detail" style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                Segera & Sangat Segera
            </div>
        </div>
    </div>
</div>

<!-- Daily Activity Chart -->
<div class="chart-section" style="margin-bottom: 2rem;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i>
                Aktivitas Harian - <?= $bulan_nama[$bulan] ?> <?= $tahun ?>
            </h3>
        </div>
        <div class="card-body">
            <canvas id="dailyChart" width="400" height="150"></canvas>
        </div>
    </div>
</div>

<!-- Weekly Summary -->
<div class="weekly-section" style="margin-bottom: 2rem;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-calendar-week"></i>
                Ringkasan Mingguan
            </h3>
        </div>
        <div class="card-body">
            <div class="weekly-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <?php foreach ($weekly_stats as $week): ?>
                <div class="week-card" style="padding: 1.5rem; background: linear-gradient(135deg, #f8fafc 0%, white 100%); border: 1px solid #e5e7eb; border-radius: 0.75rem; text-align: center;">
                    <h4 style="margin: 0 0 0.5rem; font-size: 1.1rem; font-weight: 600; color: var(--primary-color);">
                        Minggu <?= $week['week'] ?>
                    </h4>
                    <p style="margin: 0 0 1rem; color: #6b7280; font-size: 0.9rem;">
                        <?= $week['start_date'] ?> - <?= $week['end_date'] ?>
                    </p>
                    <div style="display: flex; justify-content: space-between; gap: 1rem;">
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);">
                                <?= $week['masuk'] ?>
                            </div>
                            <div style="font-size: 0.8rem; color: #6b7280;">Masuk</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);">
                                <?= $week['keluar'] ?>
                            </div>
                            <div style="font-size: 0.8rem; color: #6b7280;">Keluar</div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Status Distribution -->
<div class="status-section" style="margin-bottom: 2rem;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        
        <!-- Surat Masuk Status -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie"></i>
                    Status Surat Masuk
                </h3>
            </div>
            <div class="card-body">
                <div class="status-breakdown">
                    <div class="status-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; margin-bottom: 0.5rem; background: #f8fafc; border-radius: 0.5rem; border-left: 4px solid var(--warning-color);">
                        <div>
                            <span class="status-badge status-masuk">Masuk</span>
                            <div style="font-size: 0.85rem; color: #6b7280; margin-top: 0.25rem;">Belum diproses</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning-color);">
                                <?= number_format($masuk_stats['masuk_pending']) ?>
                            </div>
                            <div style="font-size: 0.8rem; color: #6b7280;">
                                <?= $masuk_stats['total_masuk'] > 0 ? round(($masuk_stats['masuk_pending'] / $masuk_stats['total_masuk']) * 100, 1) : 0 ?>%
                            </div>
                        </div>
                    </div>
                    
                    <div class="status-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; margin-bottom: 0.5rem; background: #f8fafc; border-radius: 0.5rem; border-left: 4px solid var(--primary-color);">
                        <div>
                            <span class="status-badge status-diproses">Diproses</span>
                            <div style="font-size: 0.85rem; color: #6b7280; margin-top: 0.25rem;">Sedang ditangani</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);">
                                <?= number_format($masuk_stats['masuk_diproses']) ?>
                            </div>
                            <div style="font-size: 0.8rem; color: #6b7280;">
                                <?= $masuk_stats['total_masuk'] > 0 ? round(($masuk_stats['masuk_diproses'] / $masuk_stats['total_masuk']) * 100, 1) : 0 ?>%
                            </div>
                        </div>
                    </div>
                    
                    <div class="status-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8fafc; border-radius: 0.5rem; border-left: 4px solid var(--success-color);">
                        <div>
                            <span class="status-badge status-selesai">Selesai</span>
                            <div style="font-size: 0.85rem; color: #6b7280; margin-top: 0.25rem;">Sudah ditangani</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);">
                                <?= number_format($masuk_stats['masuk_selesai']) ?>
                            </div>
                            <div style="font-size: 0.8rem; color: #6b7280;">
                                <?= $masuk_stats['total_masuk'] > 0 ? round(($masuk_stats['masuk_selesai'] / $masuk_stats['total_masuk']) * 100, 1) : 0 ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Surat Keluar Status -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie"></i>
                    Status Surat Keluar
                </h3>
            </div>
            <div class="card-body">
                <div class="status-breakdown">
                    <div class="status-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; margin-bottom: 0.5rem; background: #f8fafc; border-radius: 0.5rem; border-left: 4px solid #6b7280;">
                        <div>
                            <span class="status-badge status-draft">Draft</span>
                            <div style="font-size: 0.85rem; color: #6b7280; margin-top: 0.25rem;">Belum dikirim</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: #6b7280;">
                                <?= number_format($keluar_stats['keluar_draft']) ?>
                            </div>
                            <div style="font-size: 0.8rem; color: #6b7280;">
                                <?= $keluar_stats['total_keluar'] > 0 ? round(($keluar_stats['keluar_draft'] / $keluar_stats['total_keluar']) * 100, 1) : 0 ?>%
                            </div>
                        </div>
                    </div>
                    
                    <div class="status-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; margin-bottom: 0.5rem; background: #f8fafc; border-radius: 0.5rem; border-left: 4px solid var(--primary-color);">
                        <div>
                            <span class="status-badge status-dikirim">Dikirim</span>
                            <div style="font-size: 0.85rem; color: #6b7280; margin-top: 0.25rem;">Sudah dikirim</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);">
                                <?= number_format($keluar_stats['keluar_dikirim']) ?>
                            </div>
                            <div style="font-size: 0.8rem; color: #6b7280;">
                                <?= $keluar_stats['total_keluar'] > 0 ? round(($keluar_stats['keluar_dikirim'] / $keluar_stats['total_keluar']) * 100, 1) : 0 ?>%
                            </div>
                        </div>
                    </div>
                    
                    <div class="status-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8fafc; border-radius: 0.5rem; border-left: 4px solid var(--success-color);">
                        <div>
                            <span class="status-badge status-sampai">Sampai</span>
                            <div style="font-size: 0.85rem; color: #6b7280; margin-top: 0.25rem;">Sudah diterima</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);">
                                <?= number_format($keluar_stats['keluar_sampai']) ?>
                            </div>
                            <div style="font-size: 0.8rem; color: #6b7280;">
                                <?= $keluar_stats['total_keluar'] > 0 ? round(($keluar_stats['keluar_sampai'] / $keluar_stats['total_keluar']) * 100, 1) : 0 ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Correspondents -->
<div class="correspondents-section" style="margin-bottom: 2rem;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        
        <!-- Top Senders -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-building"></i>
                    Top 10 Pengirim Surat
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($top_senders)): ?>
                    <div class="empty-state" style="text-align: center; padding: 2rem; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                        <p>Belum ada data pengirim</p>
                    </div>
                <?php else: ?>
                    <div class="sender-list">
                        <?php foreach ($top_senders as $index => $sender): ?>
                        <div class="sender-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div class="rank" style="width: 24px; height: 24px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 600;">
                                    <?= $index + 1 ?>
                                </div>
                                <div>
                                    <div style="font-weight: 500; color: var(--dark-color);">
                                        <?= escape($sender['pengirim']) ?>
                                    </div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary-color);">
                                    <?= number_format($sender['count']) ?>
                                </div>
                                <div style="font-size: 0.8rem; color: #6b7280;">surat</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Receivers -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-share"></i>
                    Top 10 Penerima Surat
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($top_receivers)): ?>
                    <div class="empty-state" style="text-align: center; padding: 2rem; color: #6b7280;">
                        <i class="fas fa-paper-plane" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                        <p>Belum ada data penerima</p>
                    </div>
                <?php else: ?>
                    <div class="receiver-list">
                        <?php foreach ($top_receivers as $index => $receiver): ?>
                        <div class="receiver-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div class="rank" style="width: 24px; height: 24px; background: var(--success-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 600;">
                                    <?= $index + 1 ?>
                                </div>
                                <div>
                                    <div style="font-weight: 500; color: var(--dark-color);">
                                        <?= escape($receiver['penerima']) ?>
                                    </div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 1.2rem; font-weight: 700; color: var(--success-color);">
                                    <?= number_format($receiver['count']) ?>
                                </div>
                                <div style="font-size: 0.8rem; color: #6b7280;">surat</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Priority Distribution -->
<div class="priority-section" style="margin-bottom: 2rem;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-flag"></i>
                Distribusi Prioritas Surat
            </h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
                
                <!-- Surat Masuk Priority -->
                <div>
                    <h4 style="margin: 0 0 1rem; font-size: 1.1rem; color: var(--primary-color); text-align: center;">Surat Masuk</h4>
                    <div class="priority-chart">
                        <canvas id="masukkPriorityChart" width="300" height="200"></canvas>
                    </div>
                </div>
                
                <!-- Surat Keluar Priority -->
                <div>
                    <h4 style="margin: 0 0 1rem; font-size: 1.1rem; color: var(--success-color); text-align: center;">Surat Keluar</h4>
                    <div class="priority-chart">
                        <canvas id="keluarPriorityChart" width="300" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="action-buttons" style="text-align: center; margin-bottom: 2rem;">
    <button type="button" class="btn btn-primary" onclick="printReport()" style="margin-right: 0.5rem;">
        <i class="fas fa-print"></i>
        Cetak Laporan
    </button>
    
    <button type="button" class="btn btn-success" onclick="exportPDF()" style="margin-right: 0.5rem;">
        <i class="fas fa-file-pdf"></i>
        Export PDF
    </button>
    
    <button type="button" class="btn btn-warning" onclick="exportExcel()">
        <i class="fas fa-file-excel"></i>
        Export Excel
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Daily activity chart
    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    const dailyData = <?= json_encode($daily_stats) ?>;
    
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: dailyData.map(d => d.day),
            datasets: [{
                label: 'Surat Masuk',
                data: dailyData.map(d => d.masuk),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(59, 130, 246)',
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 4
            }, {
                label: 'Surat Keluar',
                data: dailyData.map(d => d.keluar),
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(16, 185, 129)',
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        title: function(context) {
                            const day = context[0].label;
                            const dayName = dailyData[context[0].dataIndex].day_name;
                            return `${day} ${<?= json_encode($bulan_nama[$bulan]) ?>} ${<?= $tahun ?>} (${dayName})`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Tanggal'
                    }
                }
            }
        }
    });
    
    // Priority charts
    const masukPriorityCtx = document.getElementById('masukkPriorityChart').getContext('2d');
    const keluarPriorityCtx = document.getElementById('keluarPriorityChart').getContext('2d');
    
    const masukPriorityData = [
        <?= $masuk_stats['masuk_biasa'] ?>,
        <?= $masuk_stats['masuk_penting'] ?>,
        <?= $masuk_stats['masuk_segera'] ?>,
        <?= $masuk_stats['masuk_sangat_segera'] ?>
    ];
    
    const keluarPriorityData = [
        <?= $keluar_stats['keluar_biasa'] ?>,
        <?= $keluar_stats['keluar_penting'] ?>,
        <?= $keluar_stats['keluar_segera'] ?>,
        <?= $keluar_stats['keluar_sangat_segera'] ?>
    ];
    
    const priorityLabels = ['Biasa', 'Penting', 'Segera', 'Sangat Segera'];
    const priorityColors = ['#9ca3af', '#f59e0b', '#ef4444', '#dc2626'];
    
    new Chart(masukPriorityCtx, {
        type: 'doughnut',
        data: {
            labels: priorityLabels,
            datasets: [{
                data: masukPriorityData,
                backgroundColor: priorityColors,
                borderWidth: 3,
                borderColor: 'white'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                }
            }
        }
    });
    
    new Chart(keluarPriorityCtx, {
        type: 'doughnut',
        data: {
            labels: priorityLabels,
            datasets: [{
                data: keluarPriorityData,
                backgroundColor: priorityColors,
                borderWidth: 3,
                borderColor: 'white'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                }
            }
        }
    });
});

// Export functions
function printReport() {
    window.print();
}

function exportPDF() {
    showLoading('Membuat PDF...');
    
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'pdf');
    
    const link = document.createElement('a');
    link.href = 'export.php?' + params.toString();
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    setTimeout(hideLoading, 3000);
}

function exportExcel() {
    showLoading('Membuat Excel...');
    
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'excel');
    
    const link = document.createElement('a');
    link.href = 'export.php?' + params.toString();
    link.download = `laporan_bulanan_${<?= $bulan ?>}_${<?= $tahun ?>}.xlsx`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    setTimeout(hideLoading, 3000);
}
</script>

<style>
/* Monthly report specific styles */
.week-card {
    transition: all 0.3s ease;
}

.week-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-color);
}

.status-item {
    transition: all 0.3s ease;
}

.status-item:hover {
    background: #f1f5f9 !important;
    transform: translateX(5px);
}

.sender-item:hover,
.receiver-item:hover {
    background: #f8fafc;
    transform: translateX(3px);
}

.rank {
    transition: all 0.3s ease;
}

.rank:hover {
    transform: scale(1.1);
}

/* Print styles */
@media print {
    .no-print,
    .action-buttons,
    .filter-form {
        display: none !important;
    }
    
    .chart-section {
        page-break-inside: avoid;
    }
    
    .weekly-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .priority-section .card-body > div {
        grid-template-columns: 1fr !important;
        gap: 2rem;
    }
    
    .status-section > div,
    .correspondents-section > div {
        grid-template-columns: 1fr !important;
    }
}

@media (max-width: 768px) {
    .weekly-grid {
        grid-template-columns: 1fr !important;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr !important;
    }
    
    .week-card {
        padding: 1rem;
    }
    
    .report-header {
        padding: 1.5rem;
    }
    
    .report-header h1 {
        font-size: 1.5rem !important;
    }
    
    .report-header h2 {
        font-size: 1.2rem !important;
    }
}
</style>

<?php require_once '../../includes/footer.php'; ?>