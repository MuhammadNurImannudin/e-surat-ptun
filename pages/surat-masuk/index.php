<?php
// pages/surat-masuk/index.php
require_once '../../includes/header.php';

// Handle delete action
if (isset($_GET['delete']) && isset($_GET['id'])) {
    if ($_GET['delete'] === 'confirm' && is_numeric($_GET['id'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM surat_masuk WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $_SESSION['success'] = 'Surat masuk berhasil dihapus';
            header('Location: index.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Gagal menghapus surat masuk';
        }
    }
}

// Pagination and search
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$sifat = isset($_GET['sifat']) ? $_GET['sifat'] : '';
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Build WHERE clause
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(nomor_surat LIKE ? OR pengirim LIKE ? OR perihal LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status)) {
    $where_conditions[] = "status = ?";
    $params[] = $status;
}

if (!empty($sifat)) {
    $where_conditions[] = "sifat_surat = ?";
    $params[] = $sifat;
}

if (!empty($bulan)) {
    $where_conditions[] = "MONTH(tanggal_diterima) = ?";
    $params[] = $bulan;
}

if (!empty($tahun)) {
    $where_conditions[] = "YEAR(tanggal_diterima) = ?";
    $params[] = $tahun;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM surat_masuk $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetch()['total'];
$total_pages = ceil($total_records / $per_page);

// Get surat masuk data
$sql = "SELECT sm.*, u.nama_lengkap as created_by_name 
        FROM surat_masuk sm 
        LEFT JOIN users u ON sm.created_by = u.id 
        $where_clause 
        ORDER BY sm.tanggal_diterima DESC, sm.created_at DESC 
        LIMIT $per_page OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$surat_masuk_list = $stmt->fetchAll();

// Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Masuk' THEN 1 ELSE 0 END) as masuk,
    SUM(CASE WHEN status = 'Diproses' THEN 1 ELSE 0 END) as diproses,
    SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as selesai,
    SUM(CASE WHEN MONTH(tanggal_diterima) = MONTH(CURRENT_DATE()) AND YEAR(tanggal_diterima) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as bulan_ini
    FROM surat_masuk";

$stmt = $pdo->prepare($stats_sql);
$stmt->execute();
$stats = $stmt->fetch();
?>

<!-- Enhanced CSS for Surat Masuk -->
<style>
.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card-mini {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: var(--shadow);
    border-left: 4px solid;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card-mini::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 60px;
    height: 60px;
    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3));
    border-radius: 0 0 0 60px;
}

.stat-card-mini:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.stat-card-mini.total { border-left-color: var(--primary-color); }
.stat-card-mini.masuk { border-left-color: var(--warning-color); }
.stat-card-mini.diproses { border-left-color: var(--secondary-color); }
.stat-card-mini.selesai { border-left-color: var(--success-color); }
.stat-card-mini.bulan-ini { border-left-color: var(--accent-color); }

.filter-section {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow);
    border: 1px solid #e5e7eb;
}

.filter-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.table-container {
    background: white;
    border-radius: 1rem;
    box-shadow: var(--shadow);
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.table-header {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.table-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.btn-icon {
    width: 40px;
    height: 40px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    font-size: 0.9rem;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.priority-badge {
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

.status-badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.25rem 0.6rem;
    border-radius: 0.375rem;
}

.status-masuk { background: #fef3c7; color: #92400e; }
.status-diproses { background: #dbeafe; color: #1e40af; }
.status-selesai { background: #d1fae5; color: #065f46; }

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
}

.empty-icon {
    font-size: 5rem;
    opacity: 0.3;
    margin-bottom: 1.5rem;
}

.pagination-container {
    display: flex;
    justify-content: between;
    align-items: center;
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: #f8fafc;
}

.pagination-info {
    color: #6b7280;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .table-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .stats-overview {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .table-actions {
        justify-content: center;
    }
}
</style>

<!-- Statistics Overview -->
<div class="stats-overview">
    <div class="stat-card-mini total">
        <div class="stat-icon" style="color: var(--primary-color); font-size: 1.5rem; margin-bottom: 0.5rem;">
            <i class="fas fa-inbox"></i>
        </div>
        <div class="stat-value" style="font-size: 1.8rem; font-weight: 700; color: var(--dark-color);">
            <?= number_format($stats['total']) ?>
        </div>
        <div class="stat-label" style="color: #6b7280; font-size: 0.85rem;">Total Surat</div>
    </div>

    <div class="stat-card-mini masuk">
        <div class="stat-icon" style="color: var(--warning-color); font-size: 1.5rem; margin-bottom: 0.5rem;">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-value" style="font-size: 1.8rem; font-weight: 700; color: var(--dark-color);">
            <?= number_format($stats['masuk']) ?>
        </div>
        <div class="stat-label" style="color: #6b7280; font-size: 0.85rem;">Masuk</div>
    </div>

    <div class="stat-card-mini diproses">
        <div class="stat-icon" style="color: var(--secondary-color); font-size: 1.5rem; margin-bottom: 0.5rem;">
            <i class="fas fa-cog"></i>
        </div>
        <div class="stat-value" style="font-size: 1.8rem; font-weight: 700; color: var(--dark-color);">
            <?= number_format($stats['diproses']) ?>
        </div>
        <div class="stat-label" style="color: #6b7280; font-size: 0.85rem;">Diproses</div>
    </div>

    <div class="stat-card-mini selesai">
        <div class="stat-icon" style="color: var(--success-color); font-size: 1.5rem; margin-bottom: 0.5rem;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-value" style="font-size: 1.8rem; font-weight: 700; color: var(--dark-color);">
            <?= number_format($stats['selesai']) ?>
        </div>
        <div class="stat-label" style="color: #6b7280; font-size: 0.85rem;">Selesai</div>
    </div>

    <div class="stat-card-mini bulan-ini">
        <div class="stat-icon" style="color: var(--accent-color); font-size: 1.5rem; margin-bottom: 0.5rem;">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stat-value" style="font-size: 1.8rem; font-weight: 700; color: var(--dark-color);">
            <?= number_format($stats['bulan_ini']) ?>
        </div>
        <div class="stat-label" style="color: #6b7280; font-size: 0.85rem;">Bulan Ini</div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3 style="margin: 0; color: var(--dark-color); display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-filter" style="color: var(--primary-color);"></i>
            Filter & Pencarian
        </h3>
        <button type="button" class="btn btn-primary btn-sm" onclick="resetFilters()">
            <i class="fas fa-sync-alt"></i> Reset Filter
        </button>
    </div>
    
    <form method="GET" class="filter-form">
        <div class="filter-grid">
            <div class="form-group" style="margin-bottom: 0;">
                <input type="text" name="search" class="form-control" placeholder="Cari nomor surat, pengirim, atau perihal..." value="<?= escape($search) ?>" style="padding: 0.75rem;">
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <select name="status" class="form-control form-select" style="padding: 0.75rem;">
                    <option value="">Semua Status</option>
                    <option value="Masuk" <?= $status === 'Masuk' ? 'selected' : '' ?>>Masuk</option>
                    <option value="Diproses" <?= $status === 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                    <option value="Selesai" <?= $status === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <select name="sifat" class="form-control form-select" style="padding: 0.75rem;">
                    <option value="">Semua Sifat</option>
                    <option value="Biasa" <?= $sifat === 'Biasa' ? 'selected' : '' ?>>Biasa</option>
                    <option value="Penting" <?= $sifat === 'Penting' ? 'selected' : '' ?>>Penting</option>
                    <option value="Segera" <?= $sifat === 'Segera' ? 'selected' : '' ?>>Segera</option>
                    <option value="Sangat Segera" <?= $sifat === 'Sangat Segera' ? 'selected' : '' ?>>Sangat Segera</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <select name="bulan" class="form-control form-select" style="padding: 0.75rem;">
                    <option value="">Semua Bulan</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $bulan == $m ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <select name="tahun" class="form-control form-select" style="padding: 0.75rem;">
                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                    <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem;">
                <i class="fas fa-search"></i> Filter
            </button>
        </div>
    </form>
</div>

<!-- Table Container -->
<div class="table-container">
    <div class="table-header">
        <div>
            <h3 style="margin: 0; color: var(--dark-color); display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-inbox" style="color: var(--primary-color);"></i>
                Daftar Surat Masuk
            </h3>
            <p style="margin: 0.5rem 0 0; color: #6b7280; font-size: 0.9rem;">
                Menampilkan <?= number_format($total_records) ?> surat masuk
            </p>
        </div>
        
        <div class="table-actions">
            <a href="tambah.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Surat Masuk
            </a>
            <button class="btn btn-success" onclick="exportData('excel')">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button class="btn btn-danger" onclick="exportData('pdf')">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
        </div>
    </div>

    <?php if (empty($surat_masuk_list)): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-inbox"></i>
        </div>
        <h4 style="margin-bottom: 0.5rem; color: var(--dark-color);">Tidak Ada Surat Masuk</h4>
        <p style="margin-bottom: 1.5rem;">Belum ada surat masuk yang sesuai dengan filter yang dipilih</p>
        <a href="tambah.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Surat Masuk Pertama
        </a>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="12%">No. Agenda</th>
                    <th width="15%">No. Surat</th>
                    <th width="12%">Tanggal Terima</th>
                    <th width="20%">Pengirim</th>
                    <th width="25%">Perihal</th>
                    <th width="8%">Sifat</th>
                    <th width="8%">Status</th>
                    <th width="12%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($surat_masuk_list as $index => $surat): ?>
                <tr>
                    <td><?= $offset + $index + 1 ?></td>
                    <td>
                        <strong style="color: var(--primary-color);"><?= escape($surat['nomor_agenda']) ?></strong>
                    </td>
                    <td>
                        <div style="font-weight: 600;"><?= escape($surat['nomor_surat']) ?></div>
                        <small style="color: #6b7280;"><?= formatTanggalIndo($surat['tanggal_surat']) ?></small>
                    </td>
                    <td>
                        <div style="font-weight: 500;"><?= formatTanggalIndo($surat['tanggal_diterima']) ?></div>
                        <small style="color: #6b7280;"><?= escape($surat['created_by_name']) ?></small>
                    </td>
                    <td>
                        <div style="font-weight: 500; margin-bottom: 0.25rem;"><?= escape($surat['pengirim']) ?></div>
                        <?php if (!empty($surat['alamat_pengirim'])): ?>
                        <small style="color: #6b7280; line-height: 1.3;"><?= escape($surat['alamat_pengirim']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-weight: 500; line-height: 1.3;"><?= escape($surat['perihal']) ?></div>
                        <?php if (!empty($surat['jenis_surat'])): ?>
                        <small style="color: #6b7280; margin-top: 0.25rem; display: block;">
                            <i class="fas fa-tag"></i> <?= escape($surat['jenis_surat']) ?>
                        </small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="priority-badge priority-<?= strtolower(str_replace(' ', '-', $surat['sifat_surat'])) ?>">
                            <?= $surat['sifat_surat'] ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-<?= strtolower($surat['status']) ?>">
                            <?= $surat['status'] ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="detail.php?id=<?= $surat['id'] ?>" class="btn-icon" style="background: var(--primary-color); color: white;" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="edit.php?id=<?= $surat['id'] ?>" class="btn-icon" style="background: var(--warning-color); color: white;" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deleteSurat(<?= $surat['id'] ?>, '<?= escape($surat['nomor_surat']) ?>')" class="btn-icon" style="background: var(--danger-color); color: white;" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination-container">
        <div class="pagination-info">
            Menampilkan <?= $offset + 1 ?> - <?= min($offset + $per_page, $total_records) ?> dari <?= $total_records ?> data
        </div>
        
        <div class="pagination">
            <?php
            $query_params = $_GET;
            for ($i = 1; $i <= $total_pages; $i++):
                $query_params['page'] = $i;
                $query_string = http_build_query($query_params);
                $is_current = ($i == $page);
            ?>
            <a href="?<?= $query_string ?>" class="<?= $is_current ? 'current' : '' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- JavaScript -->
<script>
function deleteSurat(id, nomor) {
    if (confirm(`Yakin ingin menghapus surat masuk dengan nomor ${nomor}?\n\nData yang sudah dihapus tidak dapat dikembalikan.`)) {
        window.location.href = `index.php?delete=confirm&id=${id}`;
    }
}

function resetFilters() {
    window.location.href = 'index.php';
}

function exportData(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('export', format);
    window.open(`export.php?${params.toString()}`, '_blank');
}

// Auto-submit form when filters change
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.filter-form');
    const selects = form.querySelectorAll('select');
    
    selects.forEach(select => {
        select.addEventListener('change', () => {
            form.submit();
        });
    });
    
    // Enhanced search with debounce
    const searchInput = form.querySelector('input[name="search"]');
    let searchTimeout;
    
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            form.submit();
        }, 800);
    });
});

// Table row click to detail
document.querySelectorAll('.table tbody tr').forEach(row => {
    row.addEventListener('click', function(e) {
        if (!e.target.closest('.action-buttons')) {
            const detailLink = this.querySelector('.btn-icon[title="Detail"]');
            if (detailLink) {
                window.location.href = detailLink.href;
            }
        }
    });
    
    row.style.cursor = 'pointer';
});
</script>

<?php require_once '../../includes/footer.php'; ?>
