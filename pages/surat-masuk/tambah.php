<?php
// pages/surat-masuk/tambah.php
require_once '../../includes/header.php';

// Handle form submission
if ($_POST) {
    try {
        $nomor_surat = trim($_POST['nomor_surat']);
        $tanggal_surat = $_POST['tanggal_surat'];
        $tanggal_diterima = $_POST['tanggal_diterima'];
        $pengirim = trim($_POST['pengirim']);
        $alamat_pengirim = trim($_POST['alamat_pengirim']);
        $perihal = trim($_POST['perihal']);
        $jenis_surat = trim($_POST['jenis_surat']);
        $sifat_surat = $_POST['sifat_surat'];
        $lampiran = trim($_POST['lampiran']);
        $disposisi = trim($_POST['disposisi']);
        $keterangan = trim($_POST['keterangan']);
        $status = $_POST['status'] ?? 'Masuk';
        
        // Generate nomor agenda
        $nomor_agenda = generateNomorAgenda('masuk');
        
        // Validation
        $errors = [];
        if (empty($nomor_surat)) $errors[] = "Nomor surat harus diisi";
        if (empty($tanggal_surat)) $errors[] = "Tanggal surat harus diisi";
        if (empty($tanggal_diterima)) $errors[] = "Tanggal diterima harus diisi";
        if (empty($pengirim)) $errors[] = "Pengirim harus diisi";
        if (empty($perihal)) $errors[] = "Perihal harus diisi";
        if (empty($jenis_surat)) $errors[] = "Jenis surat harus diisi";
        
        // Check if nomor_surat already exists
        $stmt = $pdo->prepare("SELECT id FROM surat_masuk WHERE nomor_surat = ?");
        $stmt->execute([$nomor_surat]);
        if ($stmt->fetch()) {
            $errors[] = "Nomor surat sudah ada dalam sistem";
        }
        
        if (empty($errors)) {
            // Handle file upload if present
            $file_surat = null;
            if (isset($_FILES['file_surat']) && $_FILES['file_surat']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../../uploads/surat-masuk/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_ext = strtolower(pathinfo($_FILES['file_surat']['name'], PATHINFO_EXTENSION));
                $allowed_exts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
                
                if (in_array($file_ext, $allowed_exts)) {
                    $file_name = 'SM_' . date('YmdHis') . '_' . uniqid() . '.' . $file_ext;
                    $file_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['file_surat']['tmp_name'], $file_path)) {
                        $file_surat = $file_name;
                    }
                }
            }
            
            // Insert to database
            $sql = "INSERT INTO surat_masuk (
                        nomor_surat, nomor_agenda, tanggal_surat, tanggal_diterima, 
                        pengirim, alamat_pengirim, perihal, jenis_surat, sifat_surat, 
                        lampiran, disposisi, keterangan, file_surat, status, created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $nomor_surat, $nomor_agenda, $tanggal_surat, $tanggal_diterima,
                $pengirim, $alamat_pengirim, $perihal, $jenis_surat, $sifat_surat,
                $lampiran, $disposisi, $keterangan, $file_surat, $status, $_SESSION['user_id']
            ]);
            
            if ($result) {
                $_SESSION['success'] = 'Surat masuk berhasil ditambahkan dengan nomor agenda: ' . $nomor_agenda;
                header('Location: index.php');
                exit();
            } else {
                $errors[] = "Gagal menyimpan data surat masuk";
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Terjadi kesalahan database: ' . $e->getMessage();
    }
}
?>

<style>
.form-container {
    background: white;
    border-radius: 1.5rem;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    border: 1px solid #e5e7eb;
    margin-bottom: 2rem;
}

.form-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
    color: white;
    padding: 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.form-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
}

.form-header h2 {
    margin: 0 0 0.5rem;
    font-size: 2rem;
    font-weight: 700;
    position: relative;
    z-index: 1;
}

.form-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 1.1rem;
    position: relative;
    z-index: 1;
}

.form-body {
    padding: 2.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.form-section {
    background: #f8fafc;
    border-radius: 1rem;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
}

.form-section h3 {
    margin: 0 0 1.5rem;
    color: var(--dark-color);
    font-size: 1.2rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-section h3 i {
    color: var(--primary-color);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--dark-color);
    font-size: 0.95rem;
}

.required {
    color: var(--danger-color);
    margin-left: 0.25rem;
}

.form-control {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    transform: translateY(-2px);
}

textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

.file-upload-area {
    border: 2px dashed #d1d5db;
    border-radius: 0.75rem;
    padding: 2rem;
    text-align: center;
    background: #f9fafb;
    transition: all 0.3s ease;
    cursor: pointer;
}

.file-upload-area:hover {
    border-color: var(--primary-color);
    background: #f0f9ff;
}

.file-upload-area.dragover {
    border-color: var(--success-color);
    background: #f0fdf4;
}

.upload-icon {
    font-size: 3rem;
    color: #9ca3af;
    margin-bottom: 1rem;
}

.file-info {
    display: none;
    background: white;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 1rem;
    border: 1px solid #e5e7eb;
}

.btn-group {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}

.btn {
    padding: 0.875rem 2rem;
    border-radius: 0.75rem;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    text-align: center;
    min-width: 120px;
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

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-success {
    background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
    color: white;
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
    border-left: 4px solid;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.alert-error {
    background: #fef2f2;
    color: #dc2626;
    border-left-color: var(--danger-color);
}

.alert-success {
    background: #f0fdf4;
    color: #166534;
    border-left-color: var(--success-color);
}

.form-help {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: 0.25rem;
    line-height: 1.4;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .form-body {
        padding: 1.5rem;
    }
    
    .form-header {
        padding: 1.5rem;
    }
    
    .form-header h2 {
        font-size: 1.5rem;
    }
}

.auto-generate {
    background: #f0f9ff;
    border: 1px solid #0ea5e9;
    color: #0369a1;
    padding: 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.9rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-control:disabled {
    background: #f9fafb;
    color: #6b7280;
    cursor: not-allowed;
}
</style>

<!-- Form Container -->
<div class="form-container">
    <div class="form-header">
        <h2><i class="fas fa-plus-circle"></i> Tambah Surat Masuk</h2>
        <p>Input data surat masuk baru ke dalam sistem E-Surat PTUN Banjarmasin</p>
    </div>

    <div class="form-body">
        <form method="POST" enctype="multipart/form-data" id="suratMasukForm">
            <div class="form-grid">
                <!-- Informasi Surat -->
                <div class="form-section">
                    <h3>
                        <i class="fas fa-file-alt"></i>
                        Informasi Surat
                    </h3>

                    <div class="form-group">
                        <label class="form-label">
                            Nomor Agenda
                            <div class="auto-generate">
                                <i class="fas fa-magic"></i>
                                <span>Akan dibuat otomatis: AG<?= str_pad(1, 3, '0', STR_PAD_LEFT) ?>/<?= date('Y') ?></span>
                            </div>
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="nomor_surat" class="form-label">
                            Nomor Surat <span class="required">*</span>
                        </label>
                        <input type="text" id="nomor_surat" name="nomor_surat" class="form-control" 
                               placeholder="Contoh: 001/KEMENKUMHAM/I/2024" required 
                               value="<?= escape($_POST['nomor_surat'] ?? '') ?>">
                        <div class="form-help">Masukkan nomor surat sesuai dengan dokumen asli</div>
                    </div>

                    <div class="form-group">
                        <label for="tanggal_surat" class="form-label">
                            Tanggal Surat <span class="required">*</span>
                        </label>
                        <input type="date" id="tanggal_surat" name="tanggal_surat" class="form-control" 
                               required value="<?= escape($_POST['tanggal_surat'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="tanggal_diterima" class="form-label">
                            Tanggal Diterima <span class="required">*</span>
                        </label>
                        <input type="date" id="tanggal_diterima" name="tanggal_diterima" class="form-control" 
                               required value="<?= escape($_POST['tanggal_diterima'] ?? date('Y-m-d')) ?>">
                    </div>

                    <div class="form-group">
                        <label for="jenis_surat" class="form-label">
                            Jenis Surat <span class="required">*</span>
                        </label>
                        <select id="jenis_surat" name="jenis_surat" class="form-control form-select" required>
                            <option value="">Pilih Jenis Surat</option>
                            <option value="Surat Dinas" <?= ($_POST['jenis_surat'] ?? '') === 'Surat Dinas' ? 'selected' : '' ?>>Surat Dinas</option>
                            <option value="Surat Edaran" <?= ($_POST['jenis_surat'] ?? '') === 'Surat Edaran' ? 'selected' : '' ?>>Surat Edaran</option>
                            <option value="Surat Undangan" <?= ($_POST['jenis_surat'] ?? '') === 'Surat Undangan' ? 'selected' : '' ?>>Surat Undangan</option>
                            <option value="Surat Laporan" <?= ($_POST['jenis_surat'] ?? '') === 'Surat Laporan' ? 'selected' : '' ?>>Surat Laporan</option>
                            <option value="Surat Permohonan" <?= ($_POST['jenis_surat'] ?? '') === 'Surat Permohonan' ? 'selected' : '' ?>>Surat Permohonan</option>
                            <option value="Surat Perintah" <?= ($_POST['jenis_surat'] ?? '') === 'Surat Perintah' ? 'selected' : '' ?>>Surat Perintah</option>
                            <option value="Surat Keputusan" <?= ($_POST['jenis_surat'] ?? '') === 'Surat Keputusan' ? 'selected' : '' ?>>Surat Keputusan</option>
                            <option value="Surat Pemberitahuan" <?= ($_POST['jenis_surat'] ?? '') === 'Surat Pemberitahuan' ? 'selected' : '' ?>>Surat Pemberitahuan</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="sifat_surat" class="form-label">
                            Sifat Surat <span class="required">*</span>
                        </label>
                        <select id="sifat_surat" name="sifat_surat" class="form-control form-select" required>
                            <option value="Biasa" <?= ($_POST['sifat_surat'] ?? '') === 'Biasa' ? 'selected' : '' ?>>Biasa</option>
                            <option value="Penting" <?= ($_POST['sifat_surat'] ?? '') === 'Penting' ? 'selected' : '' ?>>Penting</option>
                            <option value="Segera" <?= ($_POST['sifat_surat'] ?? '') === 'Segera' ? 'selected' : '' ?>>Segera</option>
                            <option value="Sangat Segera" <?= ($_POST['sifat_surat'] ?? '') === 'Sangat Segera' ? 'selected' : '' ?>>Sangat Segera</option>
                        </select>
                    </div>
                </div>

                <!-- Informasi Pengirim -->
                <div class="form-section">
                    <h3>
                        <i class="fas fa-building"></i>
                        Informasi Pengirim
                    </h3>

                    <div class="form-group">
                        <label for="pengirim" class="form-label">
                            Nama Pengirim <span class="required">*</span>
                        </label>
                        <input type="text" id="pengirim" name="pengirim" class="form-control" 
                               placeholder="Contoh: Kementerian Hukum dan HAM RI" required 
                               value="<?= escape($_POST['pengirim'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="alamat_pengirim" class="form-label">
                            Alamat Pengirim
                        </label>
                        <textarea id="alamat_pengirim" name="alamat_pengirim" class="form-control" 
                                  placeholder="Alamat lengkap pengirim surat"><?= escape($_POST['alamat_pengirim'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="perihal" class="form-label">
                            Perihal <span class="required">*</span>
                        </label>
                        <textarea id="perihal" name="perihal" class="form-control" 
                                  placeholder="Isi ringkasan perihal surat" required><?= escape($_POST['perihal'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="lampiran" class="form-label">
                            Lampiran
                        </label>
                        <input type="text" id="lampiran" name="lampiran" class="form-control" 
                               placeholder="Contoh: 1 berkas, 2 lembar" 
                               value="<?= escape($_POST['lampiran'] ?? '') ?>">
                        <div class="form-help">Sebutkan jumlah dan jenis lampiran jika ada</div>
                    </div>
                </div>
            </div>

            <!-- Disposisi dan File -->
            <div class="form-grid" style="margin-top: 2rem;">
                <div class="form-section">
                    <h3>
                        <i class="fas fa-comments"></i>
                        Disposisi & Keterangan
                    </h3>

                    <div class="form-group">
                        <label for="disposisi" class="form-label">
                            Disposisi
                        </label>
                        <textarea id="disposisi" name="disposisi" class="form-control" 
                                  placeholder="Instruksi disposisi dari pimpinan"><?= escape($_POST['disposisi'] ?? '') ?></textarea>
                        <div class="form-help">Diisi jika sudah ada disposisi dari pimpinan</div>
                    </div>

                    <div class="form-group">
                        <label for="keterangan" class="form-label">
                            Keterangan
                        </label>
                        <textarea id="keterangan" name="keterangan" class="form-control" 
                                  placeholder="Catatan tambahan atau keterangan khusus"><?= escape($_POST['keterangan'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label">
                            Status
                        </label>
                        <select id="status" name="status" class="form-control form-select">
                            <option value="Masuk" <?= ($_POST['status'] ?? 'Masuk') === 'Masuk' ? 'selected' : '' ?>>Masuk</option>
                            <option value="Diproses" <?= ($_POST['status'] ?? '') === 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                            <option value="Selesai" <?= ($_POST['status'] ?? '') === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                        </select>
                    </div>
                </div>

                <!-- Upload File -->
                <div class="form-section">
                    <h3>
                        <i class="fas fa-upload"></i>
                        Upload File Surat
                    </h3>

                    <div class="form-group">
                        <div class="file-upload-area" onclick="document.getElementById('file_surat').click()">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <h4 style="margin: 0 0 0.5rem; color: var(--dark-color);">Upload File Surat</h4>
                            <p style="margin: 0; color: #6b7280; font-size: 0.9rem;">
                                Klik untuk memilih file atau drag & drop di sini
                            </p>
                            <p style="margin: 0.5rem 0 0; color: #9ca3af; font-size: 0.8rem;">
                                Format: PDF, DOC, DOCX, JPG, PNG (Max: 10MB)
                            </p>
                        </div>
                        
                        <input type="file" id="file_surat" name="file_surat" 
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                               style="display: none;">
                        
                        <div class="file-info" id="file-info">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div class="file-icon" style="font-size: 2rem; color: var(--primary-color);">
                                    <i class="fas fa-file"></i>
                                </div>
                                <div>
                                    <div class="file-name" style="font-weight: 600;"></div>
                                    <div class="file-size" style="color: #6b7280; font-size: 0.9rem;"></div>
                                </div>
                                <button type="button" class="btn-remove" onclick="removeFile()" style="margin-left: auto; background: var(--danger-color); color: white; border: none; padding: 0.5rem; border-radius: 0.375rem; cursor: pointer;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-help" style="background: #fef3c7; color: #92400e; padding: 1rem; border-radius: 0.5rem; border-left: 4px solid #f59e0b;">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tips:</strong>
                        <ul style="margin: 0.5rem 0 0 1rem; padding-left: 1rem;">
                            <li>Pastikan file dapat dibaca dengan jelas</li>
                            <li>Gunakan format PDF untuk hasil terbaik</li>
                            <li>File akan disimpan dengan nama yang aman</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="btn-group">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-undo"></i> Reset
                </button>
                <button type="submit" class="btn btn-success" id="submitBtn">
                    <i class="fas fa-save"></i> Simpan Surat
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// File upload handling
document.getElementById('file_surat').addEventListener('change', handleFileSelect);

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        displayFileInfo(file);
    }
}

function displayFileInfo(file) {
    const fileInfo = document.getElementById('file-info');
    const fileName = fileInfo.querySelector('.file-name');
    const fileSize = fileInfo.querySelector('.file-size');
    
    fileName.textContent = file.name;
    fileSize.textContent = formatFileSize(file.size);
    
    fileInfo.style.display = 'block';
    
    // Update icon based on file type
    const fileIcon = fileInfo.querySelector('.file-icon i');
    const ext = file.name.split('.').pop().toLowerCase();
    
    if (ext === 'pdf') {
        fileIcon.className = 'fas fa-file-pdf';
        fileIcon.style.color = '#dc2626';
    } else if (['doc', 'docx'].includes(ext)) {
        fileIcon.className = 'fas fa-file-word';
        fileIcon.style.color = '#2563eb';
    } else if (['jpg', 'jpeg', 'png'].includes(ext)) {
        fileIcon.className = 'fas fa-file-image';
        fileIcon.style.color = '#059669';
    }
}

function removeFile() {
    document.getElementById('file_surat').value = '';
    document.getElementById('file-info').style.display = 'none';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Drag and drop functionality
const uploadArea = document.querySelector('.file-upload-area');

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('file_surat').files = files;
        displayFileInfo(files[0]);
    }
});

// Form validation and submission
document.getElementById('suratMasukForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    submitBtn.disabled = true;
    
    // Basic validation
    const requiredFields = this.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = 'var(--danger-color)';
            isValid = false;
        } else {
            field.style.borderColor = '';
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Surat';
        submitBtn.disabled = false;
        alert('Mohon lengkapi semua field yang wajib diisi');
        return;
    }
});

// Auto-resize textareas
document.querySelectorAll('textarea').forEach(textarea => {
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
});

// Set default date to today for tanggal_diterima
document.addEventListener('DOMContentLoaded', function() {
    const tanggalDiterima = document.getElementById('tanggal_diterima');
    if (!tanggalDiterima.value) {
        tanggalDiterima.value = new Date().toISOString().split('T')[0];
    }
});

// Save form data to localStorage on change (auto-save draft)
const formElements = document.querySelectorAll('input, select, textarea');
formElements.forEach(element => {
    element.addEventListener('change', function() {
        const formData = new FormData(document.getElementById('suratMasukForm'));
        const data = {};
        for (let [key, value] of formData.entries()) {
            if (key !== 'file_surat') { // Don't save file data
                data[key] = value;
            }
        }
        localStorage.setItem('surat_masuk_draft', JSON.stringify(data));
    });
});

// Load draft data on page load
window.addEventListener('load', function() {
    const draftData = localStorage.getItem('surat_masuk_draft');
    if (draftData && confirm('Ditemukan data draft. Ingin memuat data sebelumnya?')) {
        const data = JSON.parse(draftData);
        Object.keys(data).forEach(key => {
            const element = document.querySelector(`[name="${key}"]`);
            if (element) {
                element.value = data[key];
            }
        });
    }
});

// Clear draft after successful submission
document.getElementById('suratMasukForm').addEventListener('submit', function() {
    localStorage.removeItem('surat_masuk_draft');
});
</script>

<?php require_once '../../includes/footer.php'; ?>