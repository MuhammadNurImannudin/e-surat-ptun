<?php
// index.php - Dashboard E-Surat PTUN Banjarmasin
require_once 'includes/header.php';

// Get statistics data
try {
    // Total Surat Masuk
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM surat_masuk");
    $total_surat_masuk = $stmt->fetch()['total'];
    
    // Total Surat Keluar
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM surat_keluar");
    $total_surat_keluar = $stmt->fetch()['total'];
    
    // Surat Masuk Bulan Ini
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM surat_masuk WHERE MONTH(tanggal_diterima) = MONTH(CURRENT_DATE()) AND YEAR(tanggal_diterima) = YEAR(CURRENT_DATE())");
    $surat_masuk_bulan_ini = $stmt->fetch()['total'];
    
    // Surat Keluar Bulan Ini
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM surat_keluar WHERE MONTH(tanggal_keluar) = MONTH(CURRENT_DATE()) AND YEAR(tanggal_keluar) = YEAR(CURRENT_DATE())");
    $surat_keluar_bulan_ini = $stmt->fetch()['total'];
    
    // Surat Masuk Pending
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM surat_masuk WHERE status = 'Masuk'");
    $surat_masuk_pending = $stmt->fetch()['total'];
    
    // Surat Keluar Draft
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM surat_keluar WHERE status = 'Draft'");
    $surat_keluar_draft = $stmt->fetch()['total'];
    
    // Recent Surat Masuk (5 latest)
    $stmt = $pdo->query("SELECT * FROM surat_masuk ORDER BY tanggal_diterima DESC, created_at DESC LIMIT 5");
    $recent_surat_masuk = $stmt->fetchAll();
    
    // Recent Surat Keluar (5 latest)
    $stmt = $pdo->query("SELECT * FROM surat_keluar ORDER BY tanggal_keluar DESC, created_at DESC LIMIT 5");
    $recent_surat_keluar = $stmt->fetchAll();
    
    // Chart data - Surat per month (last 6 months)
    $chart_data = [];
    for ($i = 5; $i >= 0; $i--) {
        $date = date('Y-m', strtotime("-$i months"));
        $month_name = date('M Y', strtotime("-$i months"));
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as masuk FROM surat_masuk WHERE DATE_FORMAT(tanggal_diterima, '%Y-%m') = ?");
        $stmt->execute([$date]);
        $masuk = $stmt->fetch()['masuk'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as keluar FROM surat_keluar WHERE DATE_FORMAT(tanggal_keluar, '%Y-%m') = ?");
        $stmt->execute([$date]);
        $keluar = $stmt->fetch()['keluar'];
        
        $chart_data[] = [
            'month' => $month_name,
            'masuk' => $masuk,
            'keluar' => $keluar
        ];
    }
    
    // Status distribution for pie chart
    $stmt = $pdo->query("SELECT status, COUNT(*) as total FROM surat_masuk GROUP BY status");
    $status_data = [];
    while ($row = $stmt->fetch()) {
        $status_data[$row['status']] = $row['total'];
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal memuat data dashboard: " . $e->getMessage();
    
    // Set default values
    $total_surat_masuk = 0;
    $total_surat_keluar = 0;
    $surat_masuk_bulan_ini = 0;
    $surat_keluar_bulan_ini = 0;
    $surat_masuk_pending = 0;
    $surat_keluar_draft = 0;
    $recent_surat_masuk = [];
    $recent_surat_keluar = [];
    $chart_data = [];
    $status_data = ['Masuk' => 0, 'Diproses' => 0, 'Selesai' => 0];
}
?>

<!-- Dashboard Content -->
<div class="dashboard-content">
    
    <!-- Welcome Section -->
    <div class="welcome-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2.5rem; border-radius: 1.5rem; margin-bottom: 2.5rem; box-shadow: var(--shadow-xl); position: relative; overflow: hidden;">
        <div class="stat-card success">
            <div class="stat-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div class="stat-icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="stat-trend" style="font-size: 0.8rem; color: #10b981; display: flex; align-items: center; gap: 0.25rem;">
                    <i class="fas fa-arrow-up"></i>
                    <span>+<?= $surat_keluar_bulan_ini ?></span>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($total_surat_keluar) ?></div>
                <div class="stat-label">Total Surat Keluar</div>
                <div class="stat-sublabel" style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                    <?= $surat_keluar_bulan_ini ?> surat bulan ini
                </div>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-trend" style="font-size: 0.8rem; color: #f59e0b; display: flex; align-items: center; gap: 0.25rem;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Perlu Tindakan</span>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($surat_masuk_pending) ?></div>
                <div class="stat-label">Surat Masuk Pending</div>
                <div class="stat-sublabel" style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                    Menunggu disposisi
                </div>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div class="stat-icon">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="stat-trend" style="font-size: 0.8rem; color: #ef4444; display: flex; align-items: center; gap: 0.25rem;">
                    <i class="fas fa-file-alt"></i>
                    <span>Draft</span>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($surat_keluar_draft) ?></div>
                <div class="stat-label">Draft Surat Keluar</div>
                <div class="stat-sublabel" style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                    Belum dikirim
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section" style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2.5rem;">
        
        <!-- Trend Chart -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 class="card-title">
                    <i class="fas fa-chart-line" style="color: var(--primary-color);"></i>
                    Tren Surat 6 Bulan Terakhir
                </h3>
                <div class="chart-controls" style="display: flex; gap: 0.5rem;">
                    <button class="btn-chart-control active" data-chart="line" style="padding: 0.25rem 0.75rem; border: 1px solid var(--primary-color); background: var(--primary-color); color: white; border-radius: 0.375rem; font-size: 0.8rem;">
                        Line
                    </button>
                    <button class="btn-chart-control" data-chart="bar" style="padding: 0.25rem 0.75rem; border: 1px solid var(--primary-color); background: white; color: var(--primary-color); border-radius: 0.375rem; font-size: 0.8rem;">
                        Bar
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="trendChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Status Pie Chart -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie" style="color: var(--success-color);"></i>
                    Status Surat Masuk
                </h3>
            </div>
            <div class="card-body">
                <canvas id="statusChart" width="300" height="200"></canvas>
                <div class="chart-legend" style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    <div class="legend-item" style="display: flex; align-items: center; gap: 0.5rem;">
                        <span class="legend-color" style="width: 12px; height: 12px; background: #f59e0b; border-radius: 2px;"></span>
                        <span style="font-size: 0.85rem;">Masuk: <?= $status_data['Masuk'] ?? 0 ?></span>
                    </div>
                    <div class="legend-item" style="display: flex; align-items: center; gap: 0.5rem;">
                        <span class="legend-color" style="width: 12px; height: 12px; background: #3b82f6; border-radius: 2px;"></span>
                        <span style="font-size: 0.85rem;">Diproses: <?= $status_data['Diproses'] ?? 0 ?></span>
                    </div>
                    <div class="legend-item" style="display: flex; align-items: center; gap: 0.5rem;">
                        <span class="legend-color" style="width: 12px; height: 12px; background: #10b981; border-radius: 2px;"></span>
                        <span style="font-size: 0.85rem;">Selesai: <?= $status_data['Selesai'] ?? 0 ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="recent-activity" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2.5rem;">
        
        <!-- Recent Surat Masuk -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 class="card-title">
                    <i class="fas fa-inbox" style="color: var(--primary-color);"></i>
                    Surat Masuk Terbaru
                </h3>
                <a href="pages/surat-masuk/index.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-arrow-right"></i>
                    Lihat Semua
                </a>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($recent_surat_masuk)): ?>
                    <div class="empty-state" style="text-align: center; padding: 3rem 2rem; color: #6b7280;">
                        <div class="empty-icon" style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem;">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <h4 style="margin: 0 0 0.5rem; font-size: 1.1rem;">Belum Ada Surat Masuk</h4>
                        <p style="margin: 0; font-size: 0.9rem;">Surat masuk akan tampil di sini</p>
                    </div>
                <?php else: ?>
                    <div class="activity-list">
                        <?php foreach ($recent_surat_masuk as $index => $surat): ?>
                        <div class="activity-item" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; transition: all 0.3s ease; position: relative;">
                            <div class="activity-number" style="position: absolute; top: 1rem; left: 1rem; width: 24px; height: 24px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 600;">
                                <?= $index + 1 ?>
                            </div>
                            <div class="activity-content" style="margin-left: 2rem;">
                                <div class="activity-header" style="display: flex; justify-content: space-between; align-items: start; gap: 1rem; margin-bottom: 0.75rem;">
                                    <div class="activity-info">
                                        <h4 style="margin: 0 0 0.25rem; font-size: 0.95rem; font-weight: 600; color: var(--dark-color); line-height: 1.3;">
                                            <?= escape($surat['perihal']) ?>
                                        </h4>
                                        <p style="margin: 0; color: #6b7280; font-size: 0.85rem;">
                                            <i class="fas fa-building" style="width: 14px;"></i>
                                            <?= escape($surat['pengirim']) ?>
                                        </p>
                                    </div>
                                    <span class="priority-badge priority-<?= strtolower(str_replace(' ', '-', $surat['sifat_surat'])) ?>" style="padding: 0.25rem 0.6rem; border-radius: 0.375rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.025em;">
                                        <?= $surat['sifat_surat'] ?>
                                    </span>
                                </div>
                                <div class="activity-meta" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                                    <div style="display: flex; gap: 1rem; font-size: 0.8rem; color: #9ca3af;">
                                        <span>
                                            <i class="fas fa-calendar-alt"></i>
                                            <?= formatTanggalIndo($surat['tanggal_diterima']) ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-hashtag"></i>
                                            <?= escape($surat['nomor_surat']) ?>
                                        </span>
                                    </div>
                                    <span class="status-badge status-<?= strtolower($surat['status']) ?>" style="padding: 0.25rem 0.6rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 500;">
                                        <?= $surat['status'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Surat Keluar -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 class="card-title">
                    <i class="fas fa-paper-plane" style="color: var(--success-color);"></i>
                    Surat Keluar Terbaru
                </h3>
                <a href="pages/surat-keluar/index.php" class="btn btn-success btn-sm">
                    <i class="fas fa-arrow-right"></i>
                    Lihat Semua
                </a>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($recent_surat_keluar)): ?>
                    <div class="empty-state" style="text-align: center; padding: 3rem 2rem; color: #6b7280;">
                        <div class="empty-icon" style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem;">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <h4 style="margin: 0 0 0.5rem; font-size: 1.1rem;">Belum Ada Surat Keluar</h4>
                        <p style="margin: 0; font-size: 0.9rem;">Surat keluar akan tampil di sini</p>
                    </div>
                <?php else: ?>
                    <div class="activity-list">
                        <?php foreach ($recent_surat_keluar as $index => $surat): ?>
                        <div class="activity-item" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; transition: all 0.3s ease; position: relative;">
                            <div class="activity-number" style="position: absolute; top: 1rem; left: 1rem; width: 24px; height: 24px; background: var(--success-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 600;">
                                <?= $index + 1 ?>
                            </div>
                            <div class="activity-content" style="margin-left: 2rem;">
                                <div class="activity-header" style="display: flex; justify-content: space-between; align-items: start; gap: 1rem; margin-bottom: 0.75rem;">
                                    <div class="activity-info">
                                        <h4 style="margin: 0 0 0.25rem; font-size: 0.95rem; font-weight: 600; color: var(--dark-color); line-height: 1.3;">
                                            <?= escape($surat['perihal']) ?>
                                        </h4>
                                        <p style="margin: 0; color: #6b7280; font-size: 0.85rem;">
                                            <i class="fas fa-share" style="width: 14px;"></i>
                                            <?= escape($surat['penerima']) ?>
                                        </p>
                                    </div>
                                    <span class="priority-badge priority-<?= strtolower(str_replace(' ', '-', $surat['sifat_surat'])) ?>" style="padding: 0.25rem 0.6rem; border-radius: 0.375rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.025em;">
                                        <?= $surat['sifat_surat'] ?>
                                    </span>
                                </div>
                                <div class="activity-meta" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                                    <div style="display: flex; gap: 1rem; font-size: 0.8rem; color: #9ca3af;">
                                        <span>
                                            <i class="fas fa-calendar-alt"></i>
                                            <?= formatTanggalIndo($surat['tanggal_keluar']) ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-hashtag"></i>
                                            <?= escape($surat['nomor_surat']) ?>
                                        </span>
                                    </div>
                                    <span class="status-badge status-<?= strtolower($surat['status']) ?>" style="padding: 0.25rem 0.6rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 500;">
                                        <?= $surat['status'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt" style="color: var(--accent-color);"></i>
                    Aksi Cepat
                </h3>
                <p style="margin: 0.5rem 0 0; color: #6b7280; font-size: 0.9rem;">
                    Akses fitur utama dengan satu klik
                </p>
            </div>
            <div class="card-body">
                <div class="actions-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                    
                    <a href="pages/surat-masuk/tambah.php" class="action-item" style="display: flex; align-items: center; gap: 1rem; padding: 1.5rem; border: 2px solid #e5e7eb; border-radius: 1rem; text-decoration: none; color: var(--dark-color); transition: all 0.3s ease; background: linear-gradient(135deg, #f8fafc 0%, white 100%);">
                        <div class="action-icon" style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%); color: white; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0 0 0.5rem; font-size: 1.1rem; font-weight: 600;">Tambah Surat Masuk</h4>
                            <p style="margin: 0; font-size: 0.85rem; color: #6b7280; line-height: 1.4;">Input dan registrasi surat masuk baru ke dalam sistem</p>
                        </div>
                    </a>

                    <a href="pages/surat-keluar/tambah.php" class="action-item" style="display: flex; align-items: center; gap: 1rem; padding: 1.5rem; border: 2px solid #e5e7eb; border-radius: 1rem; text-decoration: none; color: var(--dark-color); transition: all 0.3s ease; background: linear-gradient(135deg, #f0fdf4 0%, white 100%);">
                        <div class="action-icon" style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%); color: white; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0 0 0.5rem; font-size: 1.1rem; font-weight: 600;">Buat Surat Keluar</h4>
                            <p style="margin: 0; font-size: 0.85rem; color: #6b7280; line-height: 1.4;">Buat dan kelola surat keluar untuk dikirim</p>
                        </div>
                    </a>

                    <a href="pages/reports/report-rekap.php" class="action-item" style="display: flex; align-items: center; gap: 1rem; padding: 1.5rem; border: 2px solid #e5e7eb; border-radius: 1rem; text-decoration: none; color: var(--dark-color); transition: all 0.3s ease; background: linear-gradient(135deg, #fffbeb 0%, white 100%);">
                        <div class="action-icon" style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%); color: white; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0 0 0.5rem; font-size: 1.1rem; font-weight: 600;">Lihat Laporan</h4>
                            <p style="margin: 0; font-size: 0.85rem; color: #6b7280; line-height: 1.4;">Rekapitulasi dan analisis data surat</p>
                        </div>
                    </a>

                    <a href="#" onclick="printDashboard()" class="action-item" style="display: flex; align-items: center; gap: 1rem; padding: 1.5rem; border: 2px solid #e5e7eb; border-radius: 1rem; text-decoration: none; color: var(--dark-color); transition: all 0.3s ease; background: linear-gradient(135deg, #fef2f2 0%, white 100%);">
                        <div class="action-icon" style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%); color: white; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">
                            <i class="fas fa-print"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0 0 0.5rem; font-size: 1.1rem; font-weight: 600;">Cetak Dashboard</h4>
                            <p style="margin: 0; font-size: 0.85rem; color: #6b7280; line-height: 1.4;">Cetak ringkasan dashboard saat ini</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Charts and Interactions -->
<script>
// Chart.js configuration
document.addEventListener('DOMContentLoaded', function() {
    
    // Chart data from PHP
    const chartData = <?= json_encode($chart_data) ?>;
    const statusData = <?= json_encode($status_data) ?>;
    
    // Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    let trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: chartData.map(d => d.month),
            datasets: [{
                label: 'Surat Masuk',
                data: chartData.map(d => d.masuk),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(59, 130, 246)',
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 6
            }, {
                label: 'Surat Keluar',
                data: chartData.map(d => d.keluar),
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(16, 185, 129)',
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 6
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
                        padding: 20
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
    
    // Chart type toggle
    document.querySelectorAll('.btn-chart-control').forEach(btn => {
        btn.addEventListener('click', function() {
            const chartType = this.dataset.chart;
            
            // Update button states
            document.querySelectorAll('.btn-chart-control').forEach(b => {
                b.classList.remove('active');
                b.style.background = 'white';
                b.style.color = 'var(--primary-color)';
            });
            
            this.classList.add('active');
            this.style.background = 'var(--primary-color)';
            this.style.color = 'white';
            
            // Update chart
            trendChart.config.type = chartType;
            trendChart.update();
        });
    });
    
    // Status Pie Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Masuk', 'Diproses', 'Selesai'],
            datasets: [{
                data: [
                    statusData['Masuk'] || 0,
                    statusData['Diproses'] || 0,
                    statusData['Selesai'] || 0
                ],
                backgroundColor: [
                    '#f59e0b',
                    '#3b82f6', 
                    '#10b981'
                ],
                borderWidth: 3,
                borderColor: 'white',
                hoverBorderWidth: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((context.parsed / total) * 100) : 0;
                            return `${context.label}: ${context.parsed} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
    
    // Action item animations
    const actionItems = document.querySelectorAll('.action-item');
    actionItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
            this.style.boxShadow = '0 12px 28px rgba(0, 0, 0, 0.15)';
            this.style.borderColor = 'var(--primary-color)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
            this.style.borderColor = '#e5e7eb';
        });
    });
    
    // Activity item animations
    const activityItems = document.querySelectorAll('.activity-item');
    activityItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8fafc';
            this.style.transform = 'translateX(4px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
            this.style.transform = 'translateX(0)';
        });
    });
    
    // Auto refresh dashboard every 5 minutes
    setInterval(() => {
        if (document.visibilityState === 'visible') {
            location.reload();
        }
    }, 300000);
});

// Print dashboard function
function printDashboard() {
    window.print();
}

// Refresh dashboard function
function refreshDashboard() {
    showLoading();
    setTimeout(() => {
        location.reload();
    }, 1000);
}
</script>

<!-- Enhanced CSS for dashboard specific elements -->
<style>
-badge {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    padding: 0.25rem 0.6rem;
    border-radius: 0.375rem;
}

.priority-biasa { background: #f3f4f6; color: #374151; }
.priority-penting { background: #fef3c7; color: #92400e; }
.priority-segera { background: #fecaca; color: #991b1b; }
.priority-sangat-segera { background: #dc2626; color: white; }

/* Status badges */
.status-masuk { background: #fef3c7; color: #92400e; }
.status-diproses { background: #dbeafe; color: #1e40af; }
.status-selesai { background: #d1fae5; color: #065f46; }
.status-draft { background: #f3f4f6; color: #374151; }
.status-dikirim { background: #dbeafe; color: #1e40af; }
.status-sampai { background: #d1fae5; color: #065f46; }

/* Chart controls */
.btn-chart-control {
    transition: all 0.3s ease;
    cursor: pointer;
    border: none;
}

.btn-chart-control:hover {
    opacity: 0.8;
    transform: translateY(-1px);
}

/* Responsive adjustments */
@media (max-width: 1200px) {
    .charts-section {
        grid-template-columns: 1fr;
    }
    
    .recent-activity {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .welcome-content {
        flex-direction: column;
        text-align: center;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    .activity-content {
        margin-left: 0;
    }
    
    .activity-number {
        display: none;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .welcome-section {
        padding: 2rem;
    }
    
    .action-item {
        padding: 1rem;
        flex-direction: column;
        text-align: center;
    }
}

/* Animation keyframes */
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

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Dashboard specific animations */
.dashboard-content {
    animation: fadeInUp 0.6s ease-out;
}

.stat-card {
    animation: fadeInUp 0.6s ease-out;
    animation-delay: 0.1s;
}

.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }

.card {
    animation: slideInLeft 0.6s ease-out;
}

.recent-activity .card:last-child {
    animation: slideInRight 0.6s ease-out;
}
</style>

<?php require_once 'includes/footer.php'; ?>welcome-bg" style="position: absolute; top: 0; right: 0; width: 200px; height: 200px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; transform: translate(50px, -50px);"></div>
        <div class="welcome-content" style="position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 2rem;">
            <div class="welcome-text">
                <h2 style="margin: 0 0 0.5rem; font-size: 2rem; font-weight: 700; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);">
                    Selamat Datang, <?= escape($current_user['nama_lengkap']) ?>!
                </h2>
                <p style="margin: 0 0 0.5rem; opacity: 0.9; font-size: 1.2rem; font-weight: 500;">
                    <?= ucfirst($current_user['level']) ?> - <?= escape($current_user['jabatan']) ?>
                </p>
                <p style="margin: 0; opacity: 0.8; font-size: 1rem; line-height: 1.6;">
                    Sistem E-Surat PTUN Banjarmasin siap membantu Anda mengelola dokumen dengan efisien dan profesional
                </p>
            </div>
            <div class="welcome-stats" style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                <div class="welcome-icon" style="font-size: 4rem; opacity: 0.7; animation: pulse 2s infinite;">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="current-time-display" style="background: rgba(255, 255, 255, 0.2); padding: 0.75rem 1.5rem; border-radius: 0.75rem; backdrop-filter: blur(10px);">
                    <div class="current-datetime" style="font-size: 0.9rem; text-align: center;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div class="stat-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <div class="stat-trend" style="font-size: 0.8rem; color: #10b981; display: flex; align-items: center; gap: 0.25rem;">
                    <i class="fas fa-arrow-up"></i>
                    <span>+<?= $surat_masuk_bulan_ini ?></span>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($total_surat_masuk) ?></div>
                <div class="stat-label">Total Surat Masuk</div>
                <div class="stat-sublabel" style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                    <?= $surat_masuk_bulan_ini ?> surat bulan ini
                </div>
            </div>
        </div>

        <div class="