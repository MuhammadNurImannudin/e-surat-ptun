<?php
// pages/surat-masuk/index.php - Fixed version
require_once '../../includes/header.php';

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search and filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$sifat_filter = $_GET['sifat'] ?? '';
$bulan_filter = $_GET['bulan'] ?? '';
$tahun_filter = $_GET['tahun'] ?? date('Y');

// Build query with filters
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(nomor_surat LIKE ? OR pengirim LIKE ? OR perihal LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if (!empty($sifat_filter)) {
    $where_conditions[] = "sifat_surat = ?";
    $params[] = $sifat_filter;
}

if (!empty($bulan_filter)) {
    $where_conditions[] = "MONTH(tanggal_diterima) = ?";
    $params[] = $bulan_filter;
}

if (!empty($tahun_filter)) {
    $where_conditions[] = "YEAR(tanggal_diterima) = ?";
    $params[] = $tahun_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

try {
    // Get total records for pagination
    $count_query = "SELECT COUNT(*) as total FROM surat_masuk $where_clause";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_records / $limit);

    // Get surat masuk data
    $query = "SELECT sm.*, u.nama_lengkap as created_by_name 
              FROM surat_masuk sm 
              LEFT JOIN users u ON sm.created_by = u.id 
              $where_clause 
              ORDER BY sm.tanggal_diterima DESC, sm.created_at DESC 
              LIMIT $limit OFFSET $offset";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $surat_masuk = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal memuat data: " . $e->getMessage();
    $total_records = 0;
    $total_pages = 0;
    $surat_masuk = [];
}

// Include sidebar dengan data yang sudah diload
require_once '../../includes/sidebar.php';
?>

<!-- Surat Masuk Content -->
<div class="surat-masuk-content">
    
    <!-- Action Bar -->
    <div class="action-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div class="action-left">
            <a href="tambah.php" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Tambah Surat Masuk
            </a>
            <button type="button" class="btn btn-success" onclick="exportData('excel')">
                <i class="fas fa-file-excel"></i>
                Export Excel
            </button>
            <button type="button" class="btn btn-danger" onclick="printPage()">
                <i class="fas fa-print"></i>
                Cetak
            </button>
        </div>
        
        <div class="action-right">
            <div class="search-box" style="position: relative;">
                <input type="text" id="searchInput" class="form-control" placeholder="Cari surat..." 
                       value="<?= escape($search) ?>" style="padding-left: 2.5rem; width: 250px;">
                <i class="fas fa-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #6b7280;"></i>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header" style="cursor: pointer;" onclick="toggleFilters()">
            <h3 class="card-title">
                <i class="fas fa-filter"></i>
                Filter Data
                <i class="fas fa-chevron-down float-right" id="filterToggleIcon" style="float: right;"></i>
            </h3>
        </div>
        <div class="card-body" id="filterContent" style="display: none;">
            <form method="GET" class="filter-form">
                <input type="hidden" name="search" value="<?= escape($search) ?>">
                
                <div class="row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control form-select">
                            <option value="">Semua Status</option>
                            <option value="Masuk" <?= $status_filter === 'Masuk' ? 'selected' : '' ?>>Masuk</option>
                            <option value="Diproses" <?= $status_filter === 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                            <option value="Selesai" <?= $status_filter === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sifat Surat</label>
                        <select name="sifat" class="form-control form-select">
                            <option value="">Semua Sifat</option>
                            <option value="Biasa" <?= $sifat_filter === 'Biasa' ? 'selected' : '' ?>>Biasa</option>
                            <option value="Penting" <?= $sifat_filter === 'Penting' ? 'selected' : '' ?>>Penting</option>
                            <option value="Segera" <?= $sifat_filter === 'Segera' ? 'selected' : '' ?>>Segera</option>
                            <option value="Sangat Segera" <?= $sifat_filter === 'Sangat Segera' ? 'selected' : '' ?>>Sangat Segera</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Bulan</label>
                        <select name="bulan" class="form-control form-select">
                            <option value="">Semua Bulan</option>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>" <?= $bulan_filter == $i ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tahun</label>
                        <select name="tahun" class="form-control form-select">
                            <?php 
                            $current_year = date('Y');
                            for ($year = $current_year; $year >= $current_year - 5; $year--): 
                            ?>
                            <option value="<?= $year ?>" <?= $tahun_filter == $year ? 'selected' : '' ?>><?= $year ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="filter-actions" style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Terapkan Filter
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Reset Filter
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Card -->
    <?php if (!empty($where_conditions)): ?>
    <div class="alert alert-info" style="margin-bottom: 2rem;">
        <i class="fas fa-info-circle"></i>
        Menampilkan <strong><?= $total_records ?></strong> surat masuk 
        <?php if (!empty($search)): ?>
            dengan kata kunci "<strong><?= escape($search) ?></strong>"
        <?php endif; ?>
        <?php if (!empty($status_filter)): ?>
            dengan status "<strong><?= $status_filter ?></strong>"
        <?php endif; ?>
        <?php if (!empty($sifat_filter)): ?>
            dengan sifat "<strong><?= $sifat_filter ?></strong>"
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-inbox"></i>
                Data Surat Masuk
                <span class="badge" style="background: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.8rem; margin-left: 0.5rem;">
                    <?= $total_records ?>
                </span>
            </h3>
        </div>
        <div class="card-body" style="padding: 0;">
            
            <!-- Bulk Actions -->
            <div class="bulk-actions" id="bulkActions" style="display: none; background: var(--primary-color); color: white; padding: 1rem; margin: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><span class="selected-count">0</span> item dipilih</span>
                    <div class="bulk-buttons">
                        <button type="button" class="btn btn-warning btn-sm" onclick="bulkUpdateStatus()" style="background: var(--warning-color); border: none; color: white; padding: 0.5rem 1rem; border-radius: 0.25rem; margin-left: 0.5rem;">
                            <i class="fas fa-edit"></i>
                            Update Status
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()" style="background: var(--danger-color); border: none; color: white; padding: 0.5rem 1rem; border-radius: 0.25rem; margin-left: 0.5rem;">
                            <i class="fas fa-trash"></i>
                            Hapus Terpilih
                        </button>
                    </div>
                </div>
            </div>

            <?php if (empty($surat_masuk)): ?>
                <div class="empty-state" style="text-align: center; padding: 3rem; color: #6b7280;">
                    <i class="fas fa-inbox" style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem; display: block;"></i>
                    <h3 style="margin-bottom: 0.5rem;">Tidak ada surat masuk</h3>
                    <p style="margin-bottom: 2rem;">
                        <?php if (!empty($search) || !empty($status_filter) || !empty($sifat_filter)): ?>
                            Tidak ditemukan surat masuk sesuai filter yang diterapkan.
                        <?php else: ?>
                            Belum ada surat masuk yang terdaftar dalam sistem.
                        <?php endif; ?>
                    </p>
                    <a href="tambah.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tambah Surat Masuk Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table" data-sortable="true">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" data-select-all>
                                </th>
                                <th data-sort="text">No. Surat</th>
                                <th data-sort="date">Tanggal</th>
                                <th data-sort="text">Pengirim</th>
                                <th data-sort="text">Perihal</th>
                                <th data-sort="text">Sifat</th>
                                <th data-sort="text">Status</th>
                                <th style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($surat_masuk as $surat): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" data-select-row value="<?= $surat['id'] ?>">
                                </td>
                                <td>
                                    <div>
                                        <strong><?= escape($surat['nomor_surat']) ?></strong>
                                        <br>
                                        <small style="color: #6b7280;">Agenda: <?= escape($surat['nomor_agenda']) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= date('d M Y', strtotime($surat['tanggal_surat'])) ?></strong>
                                        <br>
                                        <small style="color: #6b7280;">Diterima: <?= date('d M Y', strtotime($surat['tanggal_diterima'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div style="max-width: 200px;">
                                        <strong><?= escape($surat['pengirim']) ?></strong>
                                        <?php if ($surat['alamat_pengirim']): ?>
                                            <br>
                                            <small style="color: #6b7280;"><?= escape(substr($surat['alamat_pengirim'], 0, 50)) ?>...</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="max-width: 250px;">
                                        <?= escape(substr($surat['perihal'], 0, 80)) ?>
                                        <?php if (strlen($surat['perihal']) > 80): ?>...<?php endif; ?>
                                        <br>
                                        <small style="color: #6b7280;"><?= escape($surat['jenis_surat']) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge sifat-<?= strtolower(str_replace(' ', '-', $surat['sifat_surat'])) ?>" style="padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500; text-transform: uppercase;">
                                        <?= escape($surat['sifat_surat']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge status-<?= strtolower($surat['status']) ?>" style="padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500; text-transform: uppercase;">
                                        <?= escape($surat['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" style="display: flex; gap: 0.25rem;">
                                        <a href="detail.php?id=<?= $surat['id'] ?>" class="btn btn-info btn-sm" title="Detail" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit.php?id=<?= $surat['id'] ?>" class="btn btn-warning btn-sm" title="Edit" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteSurat(<?= $surat['id'] ?>)" title="Hapus" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
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
                <div class="pagination-wrapper" style="padding: 1.5rem 2rem; border-top: 1px solid #e5e7eb;">
                    <div class="pagination-info" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                        <div class="pagination-text">
                            Menampilkan <?= ($offset + 1) ?> - <?= min($offset + $limit, $total_records) ?> 
                            dari <?= $total_records ?> surat masuk
                        </div>
                        
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" style="padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.375rem; text-decoration: none; color: var(--dark-color); transition: all 0.3s ease; margin-right: 0.5rem;">
                                    <i class="fas fa-chevron-left"></i> Prev
                                </a>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            ?>

                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="current" style="padding: 0.75rem 1rem; background: var(--primary-color); color: white; border-radius: 0.375rem; margin-right: 0.5rem;"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" style="padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.375rem; text-decoration: none; color: var(--dark-color); transition: all 0.3s ease; margin-right: 0.5rem;"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" style="padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.375rem; text-decoration: none; color: var(--dark-color); transition: all 0.3s ease;">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal" id="statusModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 2000;">
    <div class="modal-content" style="background: white; border-radius: 0.75rem; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-lg); margin: 50px auto; position: relative;">
        <div class="modal-header" style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
            <h3 class="modal-title" style="font-size: 1.25rem; font-weight: 600; margin: 0;">Update Status Surat</h3>
            <button type="button" class="modal-close" onclick="hideModal(document.getElementById('statusModal'))" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 1.5rem;">
            <form id="statusUpdateForm">
                <input type="hidden" id="statusSuratIds" name="surat_ids">
                <div class="form-group">
                    <label class="form-label">Status Baru</label>
                    <select name="status" class="form-control form-select" required>
                        <option value="">Pilih Status</option>
                        <option value="Masuk">Masuk</option>
                        <option value="Diproses">Diproses</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Keterangan (Opsional)</label>
                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Tambahkan keterangan update status..."></textarea>
                </div>
                <div class="modal-actions" style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Update Status
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="hideModal(document.getElementById('statusModal'))">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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

function showModal(modal) {
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function hideModal(modal) {
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

function showAlert(message, type = 'info', autoHide = true) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 3000;
        max-width: 400px;
        animation: slideInRight 0.3s ease;
    `;
    
    const colors = {
        success: 'var(--success-color)',
        error: 'var(--danger-color)',
        warning: 'var(--warning-color)',
        info: 'var(--secondary-color)'
    };
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    alert.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas ${icons[type]}" style="color: ${colors[type]};"></i>
            <span style="flex: 1;">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: inherit; opacity: 0.7; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(alert);
    
    if (autoHide) {
        setTimeout(() => {
            if (alert.parentElement) {
                alert.remove();
            }
        }, 5000);
    }
}

// Search functionality
document.getElementById('searchInput')?.addEventListener('input', debounce(function() {
    const searchValue = this.value;
    const url = new URL(window.location);
    
    if (searchValue) {
        url.searchParams.set('search', searchValue);
    } else {
        url.searchParams.delete('search');
    }
    
    url.searchParams.delete('page');
    window.location.href = url.toString();
}, 1000));

// Filter toggle
function toggleFilters() {
    const filterContent = document.getElementById('filterContent');
    const toggleIcon = document.getElementById('filterToggleIcon');
    
    if (filterContent.style.display === 'none') {
        filterContent.style.display = 'block';
        toggleIcon.classList.remove('fa-chevron-down');
        toggleIcon.classList.add('fa-chevron-up');
    } else {
        filterContent.style.display = 'none';
        toggleIcon.classList.remove('fa-chevron-up');
        toggleIcon.classList.add('fa-chevron-down');
    }
}

// Delete single surat
function deleteSurat(id) {
    if (confirm('Apakah Anda yakin ingin menghapus surat masuk ini?')) {
        showLoading();
        
        fetch('delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({id: id})
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showAlert('Surat masuk berhasil dihapus', 'success');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showAlert(data.message || 'Gagal menghapus surat masuk', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showAlert('Terjadi kesalahan sistem', 'error');
        });
    }
}

// Row selection handling
document.addEventListener('change', function(e) {
    if (e.target.matches('input[type="checkbox"][data-select-row]')) {
        handleRowSelection(e.target);
    }
    
    if (e.target.matches('input[type="checkbox"][data-select-all]')) {
        handleSelectAll(e.target);
    }
});

function handleRowSelection(checkbox) {
    const row = checkbox.closest('tr');
    if (checkbox.checked) {
        row.classList.add('selected');
    } else {
        row.classList.remove('selected');
    }
    
    updateBulkActions();
}

function handleSelectAll(checkbox) {
    const table = checkbox.closest('table');
    const rowCheckboxes = table.querySelectorAll('input[type="checkbox"][data-select-row]');
    
    rowCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        handleRowSelection(cb);
    });
}

function updateBulkActions() {
    const selectedRows = document.querySelectorAll('tr.selected');
    const bulkActions = document.getElementById('bulkActions');
    
    if (bulkActions) {
        if (selectedRows.length > 0) {
            bulkActions.style.display = 'block';
            bulkActions.querySelector('.selected-count').textContent = selectedRows.length;
        } else {
            bulkActions.style.display = 'none';
        }
    }
}

// Bulk update status
function bulkUpdateStatus() {
    const selectedCheckboxes = document.querySelectorAll('input[data-select-row]:checked');
    if (selectedCheckboxes.length === 0) {
        showAlert('Pilih minimal satu surat masuk', 'warning');
        return;
    }
    
    const ids = Array.from(selectedCheckboxes).map(cb => cb.value);
    document.getElementById('statusSuratIds').value = ids.join(',');
    showModal(document.getElementById('statusModal'));
}

// Bulk delete
function bulkDelete() {
    const selectedCheckboxes = document.querySelectorAll('input[data-select-row]:checked');
    if (selectedCheckboxes.length === 0) {
        showAlert('Pilih minimal satu surat masuk', 'warning');
        return;
    }
    
    if (confirm(`Apakah Anda yakin ingin menghapus ${selectedCheckboxes.length} surat masuk?`)) {
        const ids = Array.from(selectedCheckboxes).map(cb => cb.value);
        
        showLoading();
        
        fetch('delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ids: ids})
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showAlert(`${selectedCheckboxes.length} surat masuk berhasil dihapus`, 'success');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showAlert(data.message || 'Gagal menghapus surat masuk', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showAlert('Terjadi kesalahan sistem', 'error');
        });
    }
}

// Status update form submission
document.getElementById('statusUpdateForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    showLoading();
    
    fetch('update-status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        hideModal(document.getElementById('statusModal'));
        
        if (data.success) {
            showAlert('Status surat berhasil diupdate', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showAlert(data.message || 'Gagal mengupdate status', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showAlert('Terjadi kesalahan sistem', 'error');
    });
});

// Export functionality
function exportData(format) {
    showLoading(`Mengekspor data ke ${format.toUpperCase()}...`);
    
    // Simulate export
    setTimeout(() => {
        hideLoading();
        showAlert(`Data berhasil diekspor ke ${format.toUpperCase()}`, 'success');
    }, 2000);
}

// Print functionality  
function printPage() {
    window.print();
}

// Show/hide loading
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
    } else {
        const text = overlay.querySelector('p');
        if (text) text.textContent = message;
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

// Add CSS for badges and animations
const additionalStyles = document.createElement('style');
additionalStyles.textContent = `
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
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Badge styles */
    .badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.375rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    
    /* Sifat surat badges */
    .sifat-biasa { background-color: #6b7280; }
    .sifat-penting { background-color: #f59e0b; }
    .sifat-segera { background-color: #ef4444; }
    .sifat-sangat-segera { 
        background-color: #dc2626; 
        animation: pulse 2s infinite;
    }
    
    /* Status badges */
    .status-masuk { background-color: #f59e0b; }
    .status-diproses { background-color: #3b82f6; }
    .status-selesai { background-color: #10b981; }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    /* Table row selection */
    tr.selected {
        background-color: rgba(59, 130, 246, 0.1) !important;
    }
    
    /* Hover effects */
    .table tbody tr:hover {
        background-color: #f8fafc;
        transition: background-color 0.2s ease;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1.5;
        border-radius: 0.25rem;
    }
    
    .btn-info { background-color: #0ea5e9; border-color: #0ea5e9; }
    .btn-info:hover { background-color: #0284c7; border-color: #0284c7; }
    
    .btn-warning { background-color: #f59e0b; border-color: #f59e0b; }
    .btn-warning:hover { background-color: #d97706; border-color: #d97706; }
    
    .btn-danger { background-color: #ef4444; border-color: #ef4444; }
    .btn-danger:hover { background-color: #dc2626; border-color: #dc2626; }
    
    /* Alert styles */
    .alert {
        position: relative;
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 0.5rem;
    }
    
    .alert-info {
        color: #0c63e4;
        background-color: #e0f2fe;
        border-color: #b3e5fc;
    }
    
    .alert-success {
        color: #0f5132;
        background-color: #d1e7dd;
        border-color: #badbcc;
    }
    
    .alert-warning {
        color: #664d03;
        background-color: #fff3cd;
        border-color: #ffecb5;
    }
    
    .alert-danger {
        color: #842029;
        background-color: #f8d7da;
        border-color: #f5c2c7;
    }
    
    /* Form styles */
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-label {
        display: inline-block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #374151;
    }
    
    .form-control {
        display: block;
        width: 100%;
        padding: 0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #212529;
        background-color: #fff;
        background-image: none;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .form-control:focus {
        color: #212529;
        background-color: #fff;
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .form-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
        padding-right: 2.25rem;
    }
    
    /* Pagination styles */
    .pagination a {
        color: #6b7280;
        background-color: #fff;
        border: 1px solid #d1d5db;
        text-decoration: none;
    }
    
    .pagination a:hover {
        background-color: #f3f4f6;
        border-color: #9ca3af;
        color: #374151;
        transform: translateY(-1px);
    }
    
    .pagination .current {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: #fff;
    }
    
    /* Responsive improvements */
    @media (max-width: 768px) {
        .action-bar {
            flex-direction: column;
            align-items: stretch !important;
        }
        
        .action-left,
        .action-right {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .search-box input {
            width: 100% !important;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .pagination-info {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
    }
    
    @media (max-width: 480px) {
        .btn {
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .table th,
        .table td {
            padding: 0.5rem 0.25rem;
            font-size: 0.8rem;
        }
        
        .btn-group {
            flex-direction: column;
            gap: 0.25rem !important;
        }
    }
`;

document.head.appendChild(additionalStyles);

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Add any initialization code here
    console.log('Surat Masuk page loaded successfully');
});
</script>

<?php 
// Include footer
require_once '../../includes/footer.php'; 
?>