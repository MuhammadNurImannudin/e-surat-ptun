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
}

require_once 'includes/sidebar.php';
?>

<!-- Dashboard Content -->
<div class="dashboard-content">
    
    <!-- Welcome Section -->
    <div class="welcome-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; border-radius: 1rem; margin-bottom: 2rem; box-shadow: var(--shadow-lg);">
        <div class="welcome-content" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="margin: 0 0 0.5rem; font-size: 1.75rem; font-weight: 700;">
                    Selamat Datang, <?= escape($current_user['nama_lengkap']) ?>!
                </h2>
                <p style="margin: 0; opacity: 0.9; font-size: 1.1rem;">
                    <?= ucfirst($current_user['level']) ?> - <?= escape($current_user['jabatan']) ?>
                </p>
                <p style="margin: 0.5rem 0 0; opacity: 0.8;">
                    Sistem E-Surat PTUN Banjarmasin siap membantu Anda mengelola dokumen dengan efisien
                </p>
            </div>
            <div class="welcome-icon" style="font-size: 4rem; opacity: 0.3;">
                <i class="fas fa-balance-scale"></i>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <div class="stat-value"><?= number_format($total_surat_masuk) ?></div>
            <div class="stat-label">Total Surat Masuk</div>
            <div class="stat-trend" style="font-size: 0.8rem; color: #10b981; margin-top: 0.5rem;">
                <i class="fas fa-arrow-up"></i>
                <?= $surat_masuk_bulan_ini ?> bulan ini
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-paper-plane"></i>
            </div>
            <div class="stat-value"><?= number_format($total_surat_keluar) ?></div>
            <div class="stat-label">Total Surat Keluar</div>
            <div class="stat-trend" style="font-size: 0.8rem; color: #10b981; margin-top: 0.5rem;">
                <i class="fas fa-arrow-up"></i>
                <?= $surat_keluar_bulan_ini ?> bulan ini
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value"><?= number_format($surat_masuk_pending) ?></div>
            <div class="stat-label">Surat Masuk Pending</div>
            <div class="stat-trend" style="font-size: 0.8rem; color: #f59e0b;">
                <i class="fas fa-exclamation-circle"></i>
                Perlu ditindaklanjuti
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-edit"></i>
            </div>
            <div class="stat-value"><?= number_format($surat_keluar_draft) ?></div>
            <div class="stat-label">Draft Surat Keluar</div>
            <div class="stat-trend" style="font-size: 0.8rem; color: #ef4444;">
                <i class="fas fa-file-alt"></i>
                Menunggu pengiriman
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section" style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        
        <!-- Line Chart -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i>
                    Tren Surat 6 Bulan Terakhir
                </h3>
            </div>
            <div class="card-body">
                <canvas id="trendChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Pie Chart -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie"></i>
                    Status Surat Masuk
                </h3>
            </div>
            <div class="card-body">
                <canvas id="statusChart" width="300" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="recent-activity" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        
        <!-- Recent Surat Masuk -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 class="card-title">
                    <i class="fas fa-inbox"></i>
                    Surat Masuk Terbaru
                </h3>
                <a href="pages/surat-masuk/index.php" class="btn btn-primary btn-sm">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($recent_surat_masuk)): ?>
                    <div class="empty-state" style="text-align: center; padding: 2rem; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                        <p>Belum ada surat masuk</p>
                    </div>
                <?php else: ?>
                    <div class="activity-list">
                        <?php foreach ($recent_surat_masuk as $surat): ?>
                        <div class="activity-item" style="padding: 1rem 2rem; border-bottom: 1px solid #e5e7eb; transition: background-color 0.3s ease;">
                            <div class="activity-content" style="display: flex; justify-content: space-between; align-items: start; gap: 1rem;">
                                <div class="activity-info">
                                    <h4 style="margin: 0 0 0.25rem; font-size: 0.95rem; font-weight: 600; color: var(--dark-color);">
                                        <?= escape($surat['perihal']) ?>
                                    </h4>
                                    <p style="margin: 0 0 0.5rem; color: #6b7280; font-size: 0.85rem;">
                                        <strong>Dari:</strong> <?= escape($surat['pengirim']) ?>
                                    </p>
                                    <div class="activity-meta" style="display: flex; gap: 1rem; font-size: 0.8rem; color: #9ca3af;">
                                        <span>
                                            <i class="fas fa-calendar"></i>
                                            <?= formatTanggalIndo($surat['tanggal_diterima']) ?>
                                        </span>
                                        <span class="status-badge status-<?= strtolower($surat['status']) ?>" style="padding: 0.25rem 0.5rem; border-radius: 0.25rem; background: #eff6ff; color: #1d4ed8;">
                                            <?= $surat['status'] ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="activity-actions">
                                    <span class="sifat-badge sifat-<?= strtolower($surat['sifat_surat']) ?>" style="padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500;">
                                        <?= $surat['sifat_surat'] ?>
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
                    <i class="fas fa-paper-plane"></i>
                    Surat Keluar Terbaru
                </h3>
                <a href="pages/surat-keluar/index.php" class="btn btn-success btn-sm">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($recent_surat_keluar)): ?>
                    <div class="empty-state" style="text-align: center; padding: 2rem; color: #6b7280;">
                        <i class="fas fa-paper-plane" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                        <p>Belum ada surat keluar</p>
                    </div>
                <?php else: ?>
                    <div class="activity-list">
                        <?php foreach ($recent_surat_keluar as $surat): ?>
                        <div class="activity-item" style="padding: 1rem 2rem; border-bottom: 1px solid #e5e7eb; transition: background-color 0.3s ease;">
                            <div class="activity-content" style="display: flex; justify-content: space-between; align-items: start; gap: 1rem;">
                                <div class="activity-info">
                                    <h4 style="margin: 0 0 0.25rem; font-size: 0.95rem; font-weight: 600; color: var(--dark-color);">
                                        <?= escape($surat['perihal']) ?>
                                    </h4>
                                    <p style="margin: 0 0 0.5rem; color: #6b7280; font-size: 0.85rem;">
                                        <strong>Kepada:</strong> <?= escape($surat['penerima']) ?>
                                    </p>
                                    <div class="activity-meta" style="display: flex; gap: 1rem; font-size: 0.8rem; color: #9ca3af;">
                                        <span>
                                            <i class="fas fa-calendar"></i>
                                            <?= formatTanggalIndo($surat['tanggal_keluar']) ?>
                                        </span>
                                        <span class="status-badge status-<?= strtolower($surat['status']) ?>" style="padding: 0.25rem 0.5rem; border-radius: 0.25rem; background: #f0fdf4; color: #166534;">
                                            <?= $surat['status'] ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="activity-actions">
                                    <span class="sifat-badge sifat-<?= strtolower($surat['sifat_surat']) ?>" style="padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500;">
                                        <?= $surat['sifat_surat'] ?>
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
    <div class="quick-actions" style="margin-top: 2rem;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt"></i>
                    Aksi Cepat
                </h3>
            </div>
            <div class="card-body">
                <div class="actions-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    
                    <a href="pages/surat-masuk/tambah.php" class="action-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; text-decoration: none; color: var(--dark-color); transition: all 0.3s ease;">
                        <div class="action-icon" style="width: 40px; height: 40px; background: var(--primary-color); color: white; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0 0 0.25rem; font-size: 0.95rem;">Tambah Surat Masuk</h4>
                            <p style="margin: 0; font-size: 0.8rem; color: #6b7280;">Input surat masuk baru</p>
                        </div>
                    </a>

                    <a href="pages/surat-keluar/tambah.php" class="action-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; text-decoration: none; color: var(--dark-color); transition: all 0.3s ease;">
                        <div class="action-icon" style="width: 40px; height: 40px; background: var(--success-color); color: white; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0 0 0.25rem; font-size: 0.95rem;">Buat Surat Keluar</h4>
                            <p style="margin: 0; font-size: 0.8rem; color: #6b7280;">Buat surat keluar baru</p>
                        </div>
                    </a>

                    <a href="pages/reports/report-rekap.php" class="action-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; text-decoration: none; color: var(--dark-color); transition: all 0.3s ease;">
                        <div class="action-icon" style="width: 40px; height: 40px; background: var(--warning-color); color: white; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0 0 0.25rem; font-size: 0.95rem;">Lihat Laporan</h4>
                            <p style="margin: 0; font-size: 0.8rem; color: #6b7280;">Rekapitulasi data surat</p>
                        </div>
                    </a>

                    <a href="#" onclick="printPage()" class="action-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; text-decoration: none; color: var(--dark-color); transition: all 0.3s ease;">
                        <div class="action-icon" style="width: 40px; height: 40px; background: var(--danger-color); color: white; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-print"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0 0 0.25rem; font-size: 0.95rem;">Print Dashboard</h4>
                            <p style="margin: 0; font-size: 0.8rem; color: #6b7280;">Cetak ringkasan dashboard</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Chart.js configuration
document.addEventListener('DOMContentLoaded', function() {
    
    // Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const chartData = <?= json_encode($chart_data) ?>;
    
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: chartData.map(d => d.month),
            datasets: [{
                label: 'Surat Masuk',
                data: chartData.map(d => d.masuk),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Surat Keluar',
                data: chartData.map(d => d.keluar),
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
    
    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Masuk', 'Diproses', 'Selesai'],
            datasets: [{
                data: [<?= $surat_masuk_pending ?>, 
                       <?= $total_surat_masuk - $surat_masuk_pending - 
                          ($pdo->query("SELECT COUNT(*) as total FROM surat_masuk WHERE status = 'Selesai'")->fetch()['total'] ?? 0) ?>, 
                       <?= $pdo->query("SELECT COUNT(*) as total FROM surat_masuk WHERE status = 'Selesai'")->fetch()['total'] ?? 0 ?>],
                backgroundColor: [
                    'rgb(245, 158, 11)',
                    'rgb(59, 130, 246)', 
                    'rgb(16, 185, 129)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Action item hover effects
    const actionItems = document.querySelectorAll('.action-item');
    actionItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.borderColor = 'var(--primary-color)';
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = 'var(--shadow-lg)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.borderColor = '#e5e7eb';
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
    
    // Activity item hover effects
    const activityItems = document.querySelectorAll('.activity-item');
    activityItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8fafc';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
});

// Status badge colors
const style = document.createElement('style');
style.textContent = `
    .status-masuk { background: #fef3c7; color: #92400e; }
    .status-diproses { background: #dbeafe; color: #1e40af; }
    .status-selesai { background: #d1fae5; color: #065f46; }
    .status-draft { background: #fef3c7; color: #92400e; }
    .status-dikirim { background: #dbeafe; color: #1e40af; }
    .status-sampai { background: #d1fae5; color: #065f46; }
    
    .sifat-biasa { background: #f3f4f6; color: #374151; }
    .sifat-penting { background: #fef3c7; color: #92400e; }
    .sifat-segera { background: #fecaca; color: #991b1b; }
    .sifat-sangat.segera { background: #dc2626; color: white; }
`;
document.head.appendChild(style);
</script>

<?php require_once 'includes/footer.php'; ?>