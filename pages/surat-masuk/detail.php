<?php
// pages/surat-masuk/detail.php
require_once '../../includes/header.php';

// Get surat ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    $_SESSION['error'] = 'ID surat tidak valid';
    header('Location: index.php');
    exit();
}

// Get surat data
try {
    $sql = "SELECT sm.*, u.nama_lengkap as created_by_name, u.jabatan as created_by_jabatan
            FROM surat_masuk sm 
            LEFT JOIN users u ON sm.created_by = u.id 
            WHERE sm.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $surat = $stmt->fetch();
    
    if (!$surat) {
        $_SESSION['error'] = 'Surat masuk tidak ditemukan';
        header('Location: index.php');
        exit();
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Gagal memuat data surat masuk';
    header('Location: index.php');
    exit();
}

// Handle status update
if ($_POST && isset($_POST['update_status'])) {
    try {
        $new_status = $_POST['status'];
        $disposisi = trim($_POST['disposisi']);
        $keterangan = trim($_POST['keterangan']);
        
        $update_sql = "UPDATE surat_masuk SET status = ?, disposisi = ?, keterangan = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($update_sql);
        $result = $stmt->execute([$new_status, $disposisi, $keterangan, $id]);
        
        if ($result) {
            $_SESSION['success'] = 'Status surat berhasil diperbarui';
            header("Location: detail.php?id=$id");
            exit();
        } else {
            $_SESSION['error'] = 'Gagal memperbarui status surat';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}
?>

<style>
.detail-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.detail-card {
    background: white;
    border-radius: 1.5rem;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.detail-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
    color: white;
    padding: 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.detail-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: repeating-linear-gradient(
        45deg,
        transparent,
        transparent 20px,
        rgba(255, 255, 255, 0.05) 20px,
        rgba(255, 255, 255, 0.05) 40px
    );
    animation: slide 30s linear infinite;
}

@keyframes slide {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

.detail-header h1 {
    margin: 0 0 0.5rem;
    font-size: 2rem;
    font-weight: 700;
    position: relative;
    z-index: 1;
}

.detail-header .badge {
    position: relative;
    z-index: 1;
    padding: 0.5rem 1.5rem;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 2rem;
    font-size: 0.9rem;
    font-weight: 600;
    display: inline-block;
}

.detail-body {
    padding: 2rem;
}

.detail-section {
    margin-bottom: 2.5rem;
}

.detail-section:last-child {
    margin-bottom: 0;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #f1f5f9;
}

.section-title h3 {
    margin: 0;
    color: var(--dark-color);
    font-size: 1.25rem;
    font-weight: 600;
}

.section-title i {
    color: var(--primary-color);
    font-size: 1.4rem;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.detail-item {
    background: #f8fafc;
    border-radius: 0.75rem;
    padding: 1.25rem;
    border-left: 4px solid var(--primary-color);
    transition: all 0.3s ease;
}

.detail-item:hover {
    background: #f1f5f9;
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.detail-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-value {
    font-size: 1rem;
    color: var(--dark-color);
    font-weight: 500;
    line-height: 1.5;
    word-wrap: break-word;
}

.detail-value.empty {
    color: #9ca3af;
    font-style: italic;
}

.priority-badge {
    display: inline-block;
    padding: 0.375rem 1rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.priority-biasa { background: #f3f4f6; color: #374151; }
.priority-penting { background: #fef3c7; color: #92400e; }
.priority-segera { background: #fecaca; color: #991b1b; }
.priority-sangat-segera { background: #dc2626; color: white; }

.status-badge {
    display: inline-block;
    padding: 0.5rem 1.25rem;
    border-radius: 2rem;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: capitalize;
}

.status-masuk { background: #fef3c7; color: #92400e; }
.status-diproses { background: #dbeafe; color: #1e40af; }
.status-selesai { background: #d1fae5; color: #065f46; }

.action-card {
    background: white;
    border-radius: 1.5rem;
    box-shadow: var(--shadow-lg);
    border: 1px solid #e5e7eb;
    position: sticky;
    top: 2rem;
    height: fit-content;
}

.action-header {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.action-header h3 {
    margin: 0;
    color: var(--dark-color);
    font-size: 1.2rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.action-body {
    padding: 1.5rem;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.btn {
    padding: 0.875rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    text-align: center;
    justify-content: center;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
}

.btn-warning {
    background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
    color: white;
}

.btn-success {
    background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
    color: white;
}

.btn-danger {
    background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
    color: white;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -2rem;
    top: 0.5rem;
    width: 12px;
    height: 12px;
    background: var(--primary-color);
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 3px #e5e7eb;
}

.timeline-content {
    background: #f8fafc;
    border-radius: 0.75rem;
    padding: 1.25rem;
    border-left: 4px solid var(--primary-color);
}

.timeline-date {
    font-size: 0.8rem;
    color: #6b7280;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.timeline-title {
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 0.25rem;
}

.timeline-description {
    color: #6b7280;
    font-size: 0.9rem;
    line-height: 1.5;
}

.file-download {
    background: linear-gradient(135deg, #f0f9ff 0%, white 100%);
    border: 2px solid #0ea5e9;
    border-radius: 1rem;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
}

.file-download:hover {
    background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%);
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
}

.file-icon {
    font-size: 3rem;
    color: #0ea5e9;
    margin-bottom: 1rem;
}

.update-status-form {
    background: #f8fafc;
    border-radius: 1rem;
    padding: 1.5rem;
    border: 1px solid #e5e7eb;
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .detail-container {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .detail-header {
        padding: 1.5rem;
    }
    
    .detail-header h1 {
        font-size: 1.5rem;
    }
    
    .detail-body {
        padding: 1.5rem;
    }
    
    .action-card {
        position: static;
    }
}

.metadata {
    background: #fffbeb;
    border: 1px solid #fcd34d;
    border-radius: 0.75rem;
    padding: 1rem;
    margin-top: 1rem;
}

.metadata-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #fde68a;
}

.metadata-item:last-child {
    border-bottom: none;
}

.metadata-label {
    font-size: 0.85rem;
    color: #92400e;
    font-weight: 600;
}

.metadata-value {
    font-size: 0.85rem;
    color: #451a03;
    font-weight: 500;
}
</style>

<!-- Detail Container -->
<div class="detail-container">
    <!-- Main Detail Card -->
    <div class="detail-card">
        <div class="detail-header">
            <h1><?= escape($surat['nomor_surat']) ?></h1>
            <div class="badge">
                Nomor Agenda: <?= escape($surat['nomor_agenda']) ?>
            </div>
        </div>

        <div class="detail-body">
            <!-- Informasi Dasar -->
            <div class="detail-section">
                <div class="section-title">
                    <i class="fas fa-info-circle"></i>
                    <h3>Informasi Dasar</h3>
                </div>
                
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-calendar"></i>
                            Tanggal Surat
                        </div>
                        <div class="detail-value"><?= formatTanggalIndo($surat['tanggal_surat']) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-calendar-check"></i>
                            Tanggal Diterima
                        </div>
                        <div class="detail-value"><?= formatTanggalIndo($surat['tanggal_diterima']) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-tag"></i>
                            Jenis Surat
                        </div>
                        <div class="detail-value"><?= escape($surat['jenis_surat']) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-exclamation-triangle"></i>
                            Sifat Surat
                        </div>
                        <div class="detail-value">
                            <span class="priority-badge priority-<?= strtolower(str_replace(' ', '-', $surat['sifat_surat'])) ?>">
                                <?= $surat['sifat_surat'] ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-tasks"></i>
                            Status
                        </div>
                        <div class="detail-value">
                            <span class="status-badge status-<?= strtolower($surat['status']) ?>">
                                <?= $surat['status'] ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if (!empty($surat['lampiran'])): ?>
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-paperclip"></i>
                            Lampiran
                        </div>
                        <div class="detail-value"><?= escape($surat['lampiran']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Informasi Pengirim -->
            <div class="detail-section">
                <div class="section-title">
                    <i class="fas fa-building"></i>
                    <h3>Informasi Pengirim</h3>
                </div>
                
                <div class="detail-grid">
                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <div class="detail-label">
                            <i class="fas fa-user-tie"></i>
                            Nama Pengirim
                        </div>
                        <div class="detail-value"><?= escape($surat['pengirim']) ?></div>
                    </div>
                    
                    <?php if (!empty($surat['alamat_pengirim'])): ?>
                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <div class="detail-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Alamat
                        </div>
                        <div class="detail-value"><?= nl2br(escape($surat['alamat_pengirim'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Perihal -->
            <div class="detail-section">
                <div class="section-title">
                    <i class="fas fa-file-alt"></i>
                    <h3>Perihal Surat</h3>
                </div>
                
                <div class="detail-item" style="border-left-color: var(--success-color);">
                    <div class="detail-value" style="font-size: 1.1rem; line-height: 1.6;">
                        <?= nl2br(escape($surat['perihal'])) ?>
                    </div>
                </div>
            </div>

            <!-- Disposisi & Keterangan -->
            <?php if (!empty($surat['disposisi']) || !empty($surat['keterangan'])): ?>
            <div class="detail-section">
                <div class="section-title">
                    <i class="fas fa-comments"></i>
                    <h3>Disposisi & Keterangan</h3>
                </div>
                
                <div class="detail-grid">
                    <?php if (!empty($surat['disposisi'])): ?>
                    <div class="detail-item" style="border-left-color: var(--warning-color); grid-column: 1 / -1;">
                        <div class="detail-label">
                            <i class="fas fa-clipboard-list"></i>
                            Disposisi
                        </div>
                        <div class="detail-value"><?= nl2br(escape($surat['disposisi'])) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($surat['keterangan'])): ?>
                    <div class="detail-item" style="border-left-color: var(--secondary-color); grid-column: 1 / -1;">
                        <div class="detail-label">
                            <i class="fas fa-sticky-note"></i>
                            Keterangan
                        </div>
                        <div class="detail-value"><?= nl2br(escape($surat['keterangan'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- File Surat -->
            <?php if (!empty($surat['file_surat'])): ?>
            <div class="detail-section">
                <div class="section-title">
                    <i class="fas fa-download"></i>
                    <h3>File Surat</h3>
                </div>
                
                <a href="../../uploads/surat-masuk/<?= escape($surat['file_surat']) ?>" 
                   class="file-download" target="_blank">
                    <div class="file-icon">
                        <?php
                        $ext = strtolower(pathinfo($surat['file_surat'], PATHINFO_EXTENSION));
                        if ($ext === 'pdf') {
                            echo '<i class="fas fa-file-pdf"></i>';
                        } elseif (in_array($ext, ['doc', 'docx'])) {
                            echo '<i class="fas fa-file-word"></i>';
                        } elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                            echo '<i class="fas fa-file-image"></i>';
                        } else {
                            echo '<i class="fas fa-file"></i>';
                        }
                        ?>
                    </div>
                    <h4 style="margin: 0 0 0.5rem; color: #0369a1;">Download File Surat</h4>
                    <p style="margin: 0; color: #0284c7; font-weight: 600;">
                        <?= escape($surat['file_surat']) ?>
                    </p>
                    <p style="margin: 0.5rem 0 0; color: #0ea5e9; font-size: 0.9rem;">
                        Klik untuk mengunduh atau melihat file
                    </p>
                </a>
            </div>
            <?php endif; ?>

            <!-- Metadata -->
            <div class="detail-section">
                <div class="section-title">
                    <i class="fas fa-info"></i>
                    <h3>Informasi Sistem</h3>
                </div>
                
                <div class="metadata">
                    <div class="metadata-item">
                        <span class="metadata-label">Dibuat oleh</span>
                        <span class="metadata-value"><?= escape($surat['created_by_name']) ?> (<?= escape($surat['created_by_jabatan']) ?>)</span>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Tanggal dibuat</span>
                        <span class="metadata-value"><?= date('d/m/Y H:i', strtotime($surat['created_at'])) ?></span>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Terakhir diperbarui</span>
                        <span class="metadata-value"><?= date('d/m/Y H:i', strtotime($surat['updated_at'])) ?></span>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">ID Surat</span>
                        <span class="metadata-value">#<?= str_pad($surat['id'], 6, '0', STR_PAD_LEFT) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Card -->
    <div class="action-card">
        <div class="action-header">
            <h3>
                <i class="fas fa-cogs"></i>
                Aksi
            </h3>
        </div>
        
        <div class="action-body">
            <div class="action-buttons">
                <a href="edit.php?id=<?= $surat['id'] ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Surat
                </a>
                
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                </a>
                
                <button onclick="printDetail()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Cetak Detail
                </button>
                
                <?php if ($current_user['level'] === 'admin' || $current_user['level'] === 'pimpinan'): ?>
                <button onclick="toggleStatusForm()" class="btn btn-success" id="updateStatusBtn">
                    <i class="fas fa-sync-alt"></i> Update Status
                </button>
                <?php endif; ?>
                
                <?php if ($current_user['level'] === 'admin'): ?>
                <button onclick="deleteSurat(<?= $surat['id'] ?>, '<?= escape($surat['nomor_surat']) ?>')" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Hapus Surat
                </button>
                <?php endif; ?>
            </div>

            <!-- Status Update Form -->
            <?php if ($current_user['level'] === 'admin' || $current_user['level'] === 'pimpinan'): ?>
            <div class="update-status-form" id="statusForm" style="display: none;">
                <form method="POST">
                    <h4 style="margin: 0 0 1rem; color: var(--dark-color);">
                        <i class="fas fa-edit"></i> Update Status & Disposisi
                    </h4>
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control form-select" required>
                            <option value="Masuk" <?= $surat['status'] === 'Masuk' ? 'selected' : '' ?>>Masuk</option>
                            <option value="Diproses" <?= $surat['status'] === 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                            <option value="Selesai" <?= $surat['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label">Disposisi</label>
                        <textarea name="disposisi" class="form-control" rows="3" 
                                  placeholder="Instruksi disposisi..."><?= escape($surat['disposisi']) ?></textarea>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2" 
                                  placeholder="Keterangan tambahan..."><?= escape($surat['keterangan']) ?></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 0.75rem;">
                        <button type="submit" name="update_status" class="btn btn-success" style="flex: 1;">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <button type="button" onclick="toggleStatusForm()" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- Timeline -->
            <div style="margin-top: 2rem;">
                <h4 style="margin: 0 0 1.5rem; color: var(--dark-color); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-history"></i> Riwayat Surat
                </h4>
                
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-content">
                            <div class="timeline-date"><?= formatTanggalIndo($surat['tanggal_surat']) ?></div>
                            <div class="timeline-title">Surat Dibuat</div>
                            <div class="timeline-description">Surat dibuat oleh <?= escape($surat['pengirim']) ?></div>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-content">
                            <div class="timeline-date"><?= formatTanggalIndo($surat['tanggal_diterima']) ?></div>
                            <div class="timeline-title">Surat Diterima</div>
                            <div class="timeline-description">
                                Surat masuk ke sistem dengan nomor agenda <?= escape($surat['nomor_agenda']) ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-content">
                            <div class="timeline-date"><?= date('d/m/Y H:i', strtotime($surat['created_at'])) ?></div>
                            <div class="timeline-title">Diinput ke Sistem</div>
                            <div class="timeline-description">
                                Data surat diinput oleh <?= escape($surat['created_by_name']) ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($surat['status'] !== 'Masuk'): ?>
                    <div class="timeline-item">
                        <div class="timeline-content">
                            <div class="timeline-date"><?= date('d/m/Y H:i', strtotime($surat['updated_at'])) ?></div>
                            <div class="timeline-title">Status: <?= $surat['status'] ?></div>
                            <div class="timeline-description">
                                Status surat diperbarui menjadi "<?= $surat['status'] ?>"
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleStatusForm() {
    const form = document.getElementById('statusForm');
    const btn = document.getElementById('updateStatusBtn');
    
    if (form.style.display === 'none') {
        form.style.display = 'block';
        btn.innerHTML = '<i class="fas fa-times"></i> Batal Update';
        btn.className = 'btn btn-secondary';
    } else {
        form.style.display = 'none';
        btn.innerHTML = '<i class="fas fa-sync-alt"></i> Update Status';
        btn.className = 'btn btn-success';
    }
}

function printDetail() {
    window.print();
}

function deleteSurat(id, nomor) {
    if (confirm(`Yakin ingin menghapus surat masuk dengan nomor ${nomor}?\n\nData yang sudah dihapus tidak dapat dikembalikan.`)) {
        window.location.href = `index.php?delete=confirm&id=${id}`;
    }
}

// Auto-resize textareas in status form
document.addEventListener('DOMContentLoaded', function() {
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
        
        // Initial resize
        textarea.style.height = textarea.scrollHeight + 'px';
    });
});

// Print styles
const printStyles = `
    <style>
        @media print {
            .action-card, .btn, .update-status-form { display: none !important; }
            .detail-container { grid-template-columns: 1fr !important; }
            .detail-card { box-shadow: none !important; border: 1px solid #000 !important; }
            .detail-header { background: #f8f9fa !important; color: #000 !important; }
            body { background: white !important; }
        }
    </style>
`;

document.head.insertAdjacentHTML('beforeend', printStyles);

// Enhanced hover effects
document.addEventListener('DOMContentLoaded', function() {
    const detailItems = document.querySelectorAll('.detail-item');
    detailItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
            this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = 'var(--shadow)';
        });
    });
});

// Status badge animation
const statusBadge = document.querySelector('.status-badge');
if (statusBadge) {
    setInterval(() => {
        if (statusBadge.classList.contains('status-masuk')) {
            statusBadge.style.animation = 'pulse 2s ease-in-out infinite';
        }
    }, 1000);
}

// Add CSS for pulse animation
const pulseAnimation = `
    <style>
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
    </style>
`;

document.head.insertAdjacentHTML('beforeend', pulseAnimation);
</script>

<?php require_once '../../includes/footer.php'; ?>