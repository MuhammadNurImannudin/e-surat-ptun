<?php
// pages/reports/report-rekap.php - Laporan Rekapitulasi Surat
$page_title = "Laporan Rekapitulasi";
$page_description = "Rekapitulasi data surat masuk dan keluar";
$breadcrumbs = [
    ['title' => 'Laporan', 'url' => '#'],
    ['title' => 'Rekapitulasi']
];

require_once '../../includes/header.php';

// Get filter parameters
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$jenis_laporan = $_GET['jenis'] ?? 'bulanan';

// Validate month and year
$bulan = max(1, min(12, (int)$bulan));
$tahun = max(2020, min(date('Y') + 1, (int)$tahun));

$bulan_nama = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

try {
    if ($jenis_laporan == 'bulanan') {
        // Data Surat Masuk Bulanan
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Masuk' THEN 1 ELSE 0 END) as masuk,
                SUM(CASE WHEN status = 'Diproses' THEN 1 ELSE 0 END) as diproses,
                SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN sifat_surat = 'Biasa' THEN 1 ELSE 0 END) as biasa,
                SUM(CASE WHEN sifat_surat = 'Penting' THEN 1 ELSE 0 END) as penting,
                SUM(CASE WHEN sifat_surat = 'Segera' THEN 1 ELSE 0 END) as segera,
                SUM(CASE WHEN sifat_surat = 'Sangat Segera' THEN 1 ELSE 0 END) as sangat_segera
            FROM surat_masuk 
            WHERE MONTH(tanggal_diterima) = ? AND YEAR(tanggal_diterima) = ?
        ");
        $stmt->execute([$bulan, $tahun]);
        $data_masuk = $stmt->fetch();

        // Data Surat Keluar Bulanan
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN status = 'Dikirim' THEN 1 ELSE 0 END) as dikirim,
                SUM(CASE WHEN status = 'Sampai' THEN 1 ELSE 0 END) as sampai,
                SUM(CASE WHEN sifat_surat = 'Biasa' THEN 1 ELSE 0 END) as biasa,
                SUM(CASE WHEN sifat_surat = 'Penting' THEN 1 ELSE 0 END) as penting,
                SUM(CASE WHEN sifat_surat = 'Segera' THEN 1 ELSE 0 END) as segera,
                SUM(CASE WHEN sifat_surat = 'Sangat Segera' THEN 1 ELSE 0 END) as sangat_segera
            FROM surat_keluar 
            WHERE MONTH(tanggal_keluar) = ? AND YEAR(tanggal_keluar) = ?
        ");
        $stmt->execute([$bulan, $tahun]);
        $data_keluar = $stmt->fetch();

        // Detail Surat Masuk
        $stmt = $pdo->prepare("
            SELECT sm.*, u.nama_lengkap as input_by
            FROM surat_masuk sm
            LEFT JOIN users u ON sm.created_by = u.id
            WHERE MONTH(sm.tanggal_diterima) = ? AND YEAR(sm.tanggal_diterima) = ?
            ORDER BY sm.tanggal_diterima DESC
        ");
        $stmt->execute([$bulan, $tahun]);
        $detail_masuk = $stmt->fetchAll();

        // Detail Surat Keluar
        $stmt = $pdo->prepare("
            SELECT sk.*, u.nama_lengkap as input_by
            FROM surat_keluar sk
            LEFT JOIN users u ON sk.created_by = u.id
            WHERE MONTH(sk.tanggal_keluar) = ? AND YEAR(sk.tanggal_keluar) = ?
            ORDER BY sk.tanggal_keluar DESC
        ");
        $stmt->execute([$bulan, $tahun]);
        $detail_keluar = $stmt->fetchAll();

        $periode_text = $bulan_nama[$bulan] . ' ' . $tahun;
    } else {
        // Data Tahunan
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Masuk' THEN 1 ELSE 0 END) as masuk,
                SUM(CASE WHEN status = 'Diproses' THEN 1 ELSE 0 END) as diproses,
                SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN sifat_surat = 'Biasa' THEN 1 ELSE 0 END) as biasa,
                SUM(CASE WHEN sifat_surat = 'Penting' THEN 1 ELSE 0 END) as penting,
                SUM(CASE WHEN sifat_surat = 'Segera' THEN 1 ELSE 0 END) as segera,
                SUM(CASE WHEN sifat_surat = 'Sangat Segera' THEN 1 ELSE 0 END) as sangat_segera
            FROM surat_masuk 
            WHERE YEAR(tanggal_diterima) = ?
        ");
        $stmt->execute([$tahun]);
        $data_masuk = $stmt->fetch();

        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN status = 'Dikirim' THEN 1 ELSE 0 END) as dikirim,
                SUM(CASE WHEN status = 'Sampai' THEN 1 ELSE 0 END) as sampai,
                SUM(CASE WHEN sifat_surat = 'Biasa' THEN 1 ELSE 0 END) as biasa,
                SUM(CASE WHEN sifat_surat = 'Penting' THEN 1 ELSE 0 END) as penting,
                SUM(CASE WHEN sifat_surat = 'Segera' THEN 1 ELSE 0 END) as segera,
                SUM(CASE WHEN sifat_surat = 'Sangat Segera' THEN 1 ELSE 0 END) as sangat_segera
            FROM surat_keluar 
            WHERE YEAR(tanggal_keluar) = ?
        ");
        $stmt->execute([$tahun]);
        $data_keluar = $stmt->fetch();

        // Data per bulan untuk chart
        $chart_data = [];
        for ($i = 1; $i <= 12; $i++) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as masuk FROM surat_masuk WHERE MONTH(tanggal_diterima) = ? AND YEAR(tanggal_diterima) = ?");
            $stmt->execute([$i, $tahun]);
            $masuk_count = $stmt->fetch()['masuk'];

            $stmt = $pdo->prepare("SELECT COUNT(*) as keluar FROM surat_keluar WHERE MONTH(tanggal_keluar) = ? AND YEAR(tanggal_keluar) = ?");
            $stmt->execute([$i, $tahun]);
            $keluar_count = $stmt->fetch()['keluar'];

            $chart_data[] = [
                'bulan' => $bulan_nama[$i],
                'masuk' => $masuk_count,
                'keluar' => $keluar_count
            ];
        }

        $periode_text = 'Tahun ' . $tahun;
        $detail_masuk = [];
        $detail_keluar = [];
    }

} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal memuat data laporan: " . $e->getMessage();
    $data_masuk = ['total' => 0, 'masuk' => 0, 'diproses' => 0, 'selesai' => 0, 'biasa' => 0, 'penting' => 0, 'segera' => 0, 'sangat_segera' => 0];
    $data_keluar = ['total' => 0, 'draft' => 0, 'dikirim' => 0, 'sampai' => 0, 'biasa' => 0, 'penting' => 0, 'segera' => 0, 'sangat_segera' => 0];
    $detail_masuk = [];
    $detail_keluar = [];
    $chart_data = [];
}
?>

<!-- Filter Section -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-filter"></i>
            Filter Laporan
        </h3>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <div class="form-group">
                    <label class="form-label">Jenis Laporan</label>
                    <select name="jenis" class="form-control form-select" onchange="togglePeriode()">
                        <option value="bulanan" <?= $jenis_laporan == 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                        <option value="tahunan" <?= $jenis_laporan == 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
                    </select>
                </div>
                
                <div class="form-group" id="bulan-group" <?= $jenis_laporan == 'tahunan' ? 'style="display:none;"' : '' ?>>
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
<div class="report-header" style="text-align: center; margin-bottom: 2rem; padding: 2rem; background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%); color: white; border-radius: 1rem;">
    <div class="report-logo" style="margin-bottom: 1rem;">
        <i class="fas fa-balance-scale" style="font-size: 3rem; opacity: 0.8;"></i>
    </div>
    <h1 style="margin: 0 0 0.5rem; font-size: 2rem; font-weight: 700;">
        Laporan Rekapitulasi Surat
    </h1>
    <h2 style="margin: 0 0 1rem; font-size: 1.5rem; font-weight: 500;">
        Pengadilan Tata Usaha Negara Banjarmasin
    </h2>
    <p style="margin: 0; font-size: 1.2rem; opacity: 0.9;">
        Periode: <?= $periode_text ?>
    </p>
</div>

<!-- Summary Statistics -->
<div class="summary-section" style="margin-bottom: 2rem;">
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <div class="stat-value"><?= number_format($data_masuk['total']) ?></div>
            <div class="stat-label">Total Surat Masuk</div>
            <div class="stat-detail" style="font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem;">
                Selesai: <?= $data_masuk['selesai'] ?> | Proses: <?= $data_masuk['diproses'] ?> | Baru: <?= $data_masuk['masuk'] ?>
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-paper-plane"></i>
            </div>
            <div class="stat-value"><?= number_format($data_keluar['total']) ?></div>
            <div class="stat-label">Total Surat Keluar</div>
            <div class="stat-detail" style="font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem;">
                Sampai: <?= $data_keluar['sampai'] ?> | Dikirim: <?= $data_keluar['dikirim'] ?> | Draft: <?= $data_keluar['draft'] ?>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-value"><?= number_format($data_masuk['segera'] + $data_masuk['sangat_segera'] + $data_keluar['segera'] + $data_keluar['sangat_segera']) ?></div>
            <div class="stat-label">Surat Prioritas</div>
            <div class="stat-detail" style="font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem;">
                Sangat Segera: <?= $data_masuk['sangat_segera'] + $data_keluar['sangat_segera'] ?> | Segera: <?= $data_masuk['segera'] + $data_keluar['segera'] ?>
            </div>
        </div>
        
        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="stat-value"><?= $data_masuk['total'] + $data_keluar['total'] > 0 ? number_format((($data_masuk['selesai'] + $data_keluar['sampai']) / ($data_masuk['total'] + $data_keluar['total'])) * 100, 1) : 0 ?>%</div>
            <div class="stat-label">Tingkat Penyelesaian</div>
            <div class="stat-detail" style="font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem;">
                <?= $data_masuk['selesai'] + $data_keluar['sampai'] ?> dari <?= $data_masuk['total'] + $data_keluar['total'] ?> surat
            </div>
        </div>
    </div>
</div>

<!-- Charts Section (for yearly report) -->
<?php if ($jenis_laporan == 'tahunan' && !empty($chart_data)): ?>
<div class="chart-section" style="margin-bottom: 2rem;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i>
                Grafik Surat Per Bulan - <?= $tahun ?>
            </h3>
        </div>
        <div class="card-body">
            <canvas id="monthlyChart" width="400" height="150"></canvas>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Detailed Tables (for monthly report) -->
<?php if ($jenis_laporan == 'bulanan'): ?>
<div class="detail-section" style="margin-bottom: 2rem;">
    <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        
        <!-- Surat Masuk Detail -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-inbox"></i>
                    Detail Surat Masuk - <?= $periode_text ?>
                </h3>
                <p style="margin: 0.5rem 0 0; color: #6b7280; font-size: 0.9rem;">
                    Total: <?= count($detail_masuk) ?> surat masuk
                </p>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($detail_masuk)): ?>
                    <div class="empty-state" style="padding: 2rem; text-align: center; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                        <p>Tidak ada surat masuk pada periode ini</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm">
                            <thead style="position: sticky; top: 0; background: white; z-index: 10;">
                                <tr>
                                    <th>No</th>
                                    <th>Nomor Surat</th>
                                    <th>Tanggal</th>
                                    <th>Pengirim</th>
                                    <th>Status</th>
                                    <th>Sifat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detail_masuk as $index => $surat): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td style="font-size: 0.85rem;">
                                        <div style="font-weight: 600;"><?= escape($surat['nomor_surat']) ?></div>
                                        <small style="color: #6b7280;"><?= escape($surat['nomor_agenda']) ?></small>
                                    </td>
                                    <td style="font-size: 0.85rem;"><?= formatTanggalIndo($surat['tanggal_diterima']) ?></td>
                                    <td style="font-size: 0.85rem; max-width: 150px;">
                                        <?= escape(strlen($surat['pengirim']) > 20 ? substr($surat['pengirim'], 0, 20) . '...' : $surat['pengirim']) ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower($surat['status']) ?>" style="font-size: 0.7rem;">
                                            <?= $surat['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="priority-badge priority-<?= strtolower(str_replace(' ', '-', $surat['sifat_surat'])) ?>" style="font-size: 0.7rem;">
                                            <?= $surat['sifat_surat'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Surat Keluar Detail -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-paper-plane"></i>
                    Detail Surat Keluar - <?= $periode_text ?>
                </h3>
                <p style="margin: 0.5rem 0 0; color: #6b7280; font-size: 0.9rem;">
                    Total: <?= count($detail_keluar) ?> surat keluar
                </p>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($detail_keluar)): ?>
                    <div class="empty-state" style="padding: 2rem; text-align: center; color: #6b7280;">
                        <i class="fas fa-paper-plane" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                        <p>Tidak ada surat keluar pada periode ini</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm">
                            <thead style="position: sticky; top: 0; background: white; z-index: 10;">
                                <tr>
                                    <th>No</th>
                                    <th>Nomor Surat</th>
                                    <th>Tanggal</th>
                                    <th>Penerima</th>
                                    <th>Status</th>
                                    <th>Sifat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detail_keluar as $index => $surat): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td style="font-size: 0.85rem;">
                                        <div style="font-weight: 600;"><?= escape($surat['nomor_surat']) ?></div>
                                        <small style="color: #6b7280;"><?= escape($surat['nomor_agenda']) ?></small>
                                    </td>
                                    <td style="font-size: 0.85rem;"><?= formatTanggalIndo($surat['tanggal_keluar']) ?></td>
                                    <td style="font-size: 0.85rem; max-width: 150px;">
                                        <?= escape(strlen($surat['penerima']) > 20 ? substr($surat['penerima'], 0, 20) . '...' : $surat['penerima']) ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower($surat['status']) ?>" style="font-size: 0.7rem;">
                                            <?= $surat['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="priority-badge priority-<?= strtolower(str_replace(' ', '-', $surat['sifat_surat'])) ?>" style="font-size: 0.7rem;">
                                            <?= $surat['sifat_surat'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Breakdown by Priority -->
<div class="priority-section" style="margin-bottom: 2rem;">
    <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie"></i>
                    Distribusi Sifat Surat Masuk
                </h3>
            </div>
            <div class="card-body">
                <div class="priority-stats">
                    <div class="priority-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; margin-bottom: 0.5rem; background: #f8fafc; border-radius: 0.5rem;">
                        <span><span class="priority-badge priority-biasa" style="font-size: 0.75rem;">Biasa</span></span>
                        <strong><?= number_format($data_masuk['biasa']) ?> surat</strong>
                    </div>
                    <div class="priority-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; margin-bottom: 0.5rem; background: #f8fafc; border-radius: 0.5rem;">
                        <span><span class="priority-badge priority-penting" style="font-size: 0.75rem;">Penting</span></span>
                        <strong><?= number_format($data_masuk['penting']) ?> surat</strong>
                    </div>
                    <div class="priority-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; margin-bottom: 0.5rem; background: #f8fafc; border-radius: 0.5rem;">
                        <span><span class="priority-badge priority-segera" style="font-size: 0.75rem;">Segera</span></span>
                        <strong><?= number_format($data_masuk['segera']) ?> surat</strong>
                    </div>
                    <div class="priority-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #f8fafc; border-radius: 0.5rem;">
                        <span><span class="priority-badge priority-sangat-segera" style="font-size: 0.75rem;">Sangat Segera</span></span>
                        <strong><?= number_format($data_masuk['sangat_segera']) ?> surat</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie"></i>
                    Distribusi Sifat Surat Keluar
                </h3>
            </div>
            <div class="card-body">
                <div class="priority-stats">
                    <div class="priority-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; margin-bottom: 0.5rem; background: #f8fafc; border-radius: 0.5rem;">
                        <span><span class="priority-badge priority-biasa" style="font-size: 0.75rem;">Biasa</span></span>
                        <strong><?= number_format($data_keluar['biasa']) ?> surat</strong>
                    </div>
                    <div class="priority-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; margin-bottom: 0.5rem; background: #f8fafc; border-radius: 0.5rem;">
                        <span><span class="priority-badge priority-penting" style="font-size: 0.75rem;">Penting</span></span>
                        <strong><?= number_format($data_keluar['penting']) ?> surat</strong>
                    </div>
                    <div class="priority-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; margin-bottom: 0.5rem; background: #f8fafc; border-radius: 0.5rem;">
                        <span><span class="priority-badge priority-segera" style="font-size: 0.75rem;">Segera</span></span>
                        <strong><?= number_format($data_keluar['segera']) ?> surat</strong>
                    </div>
                    <div class="priority-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #f8fafc; border-radius: 0.5rem;">
                        <span><span class="priority-badge priority-sangat-segera" style="font-size: 0.75rem;">Sangat Segera</span></span>
                        <strong><?= number_format($data_keluar['sangat_segera']) ?> surat</strong>
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

<!-- Report Footer -->
<div class="report-footer" style="text-align: right; padding: 2rem; border-top: 2px solid #e5e7eb; margin-top: 3rem;">
    <div class="signature-section" style="max-width: 300px; margin-left: auto;">
        <p style="margin: 0 0 3rem; font-weight: 600;">
            Banjarmasin, <?= formatTanggalIndo(date('Y-m-d')) ?>
        </p>
        <p style="margin: 0 0 0.5rem; font-weight: 600;">
            Kepala Pengadilan Tata Usaha Negara Banjarmasin
        </p>
        <div style="height: 80px; margin: 2rem 0;"></div>
        <p style="margin: 0; font-weight: 600; text-decoration: underline;">
            Dr. Ahmad Fauzi, S.H., M.H.
        </p>
        <p style="margin: 0; font-size: 0.9rem;">
            NIP. 196803081990031001
        </p>
    </div>
</div>

<script>
<?php if ($jenis_laporan == 'tahunan' && !empty($chart_data)): ?>
// Chart for yearly report
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    
    const chartData = <?= json_encode($chart_data) ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(d => d.bulan),
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
});
<?php endif; ?>

// Toggle periode visibility
function togglePeriode() {
    const jenisLaporan = document.querySelector('select[name="jenis"]').value;
    const bulanGroup = document.getElementById('bulan-group');
    
    if (jenisLaporan === 'tahunan') {
        bulanGroup.style.display = 'none';
    } else {
        bulanGroup.style.display = 'block';
    }
}

// Print report
function printReport() {
    window.print();
}

// Export to PDF
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

// Export to Excel
function exportExcel() {
    showLoading('Membuat Excel...');
    
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'excel');
    
    const link = document.createElement('a');
    link.href = 'export.php?' + params.toString();
    link.download = `laporan_rekap_${new Date().toISOString().slice(0, 10)}.xlsx`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    setTimeout(hideLoading, 3000);
}
</script>

<style>
/* Print styles for report */
@media print {
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
        page-break-inside: avoid;
        margin-bottom: 1rem !important;
    }
    
    .card-header {
        background: #f8f9fa !important;
        border-bottom: 1px solid #000 !important;
    }
    
    .no-print,
    .filter-form,
    .action-buttons,
    .btn {
        display: none !important;
    }
    
    .report-header {
        background: #f8f9fa !important;
        color: #000 !important;
        border: 2px solid #000 !important;
    }
    
    .stat-card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
    
    .table th,
    .table td {
        border: 1px solid #000 !important;
        font-size: 10pt !important;
    }
    
    .table th {
        background: #f8f9fa !important;
    }
    
    .priority-badge,
    .status-badge {
        border: 1px solid #000 !important;
    }
    
    .signature-section {
        page-break-inside: avoid;
    }
}
</style>

<?php require_once '../../includes/footer.php'; ?>