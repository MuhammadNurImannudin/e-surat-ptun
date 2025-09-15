<?php
// pages/settings/index.php - System Settings
$page_title = "Pengaturan Sistem";
$page_description = "Konfigurasi dan pengaturan aplikasi E-Surat";
$breadcrumbs = [
    ['title' => 'Pengaturan']
];

require_once '../../includes/header.php';

// Check admin access
if ($current_user['level'] !== 'admin') {
    $_SESSION['error'] = "Akses ditolak. Hanya administrator yang dapat mengakses halaman ini.";
    header('Location: ../../index.php');
    exit();
}

// Create settings table if not exists
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS app_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            setting_type ENUM('text', 'number', 'boolean', 'select', 'textarea') DEFAULT 'text',
            setting_group VARCHAR(50) DEFAULT 'general',
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Insert default settings if not exists
    $default_settings = [
        ['app_name', 'E-Surat PTUN Banjarmasin', 'text', 'general', 'Nama Aplikasi'],
        ['app_version', '1.0.0', 'text', 'general', 'Versi Aplikasi'],
        ['institution_name', 'Pengadilan Tata Usaha Negara Banjarmasin', 'text', 'general', 'Nama Institusi'],
        ['institution_address', 'Jl. RE Martadinata No.1, Kertak Baru Ilir, Banjarmasin', 'textarea', 'general', 'Alamat Institusi'],
        ['institution_phone', '(0511) 3252735', 'text', 'general', 'Telepon Institusi'],
        ['institution_email', 'info@ptun-banjarmasin.go.id', 'text', 'general', 'Email Institusi'],
        ['head_name', 'Dr. Ahmad Fauzi, S.H., M.H.', 'text', 'general', 'Nama Pimpinan'],
        ['head_nip', '196803081990031001', 'text', 'general', 'NIP Pimpinan'],
        ['max_file_size', '5', 'number', 'upload', 'Maksimal Ukuran File (MB)'],
        ['allowed_extensions', 'pdf,doc,docx,jpg,png', 'text', 'upload', 'Ekstensi File yang Diizinkan'],
        ['auto_backup', '1', 'boolean', 'system', 'Backup Otomatis'],
        ['backup_frequency', '7', 'number', 'system', 'Frekuensi Backup (Hari)'],
        ['session_timeout', '120', 'number', 'security', 'Timeout Session (Menit)'],
        ['enable_registration', '0', 'boolean', 'security', 'Aktifkan Registrasi Pengguna'],
        ['min_password_length', '6', 'number', 'security', 'Panjang Password Minimal'],
        ['theme_color', 'blue', 'select', 'appearance', 'Warna Tema'],
        ['items_per_page', '10', 'number', 'display', 'Jumlah Item per Halaman'],
        ['date_format', 'dd/mm/yyyy', 'select', 'display', 'Format Tanggal'],
        ['timezone', 'Asia/Makassar', 'select', 'display', 'Zona Waktu']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO app_settings (setting_key, setting_value, setting_type, setting_group, description) VALUES (?, ?, ?, ?, ?)");
    foreach ($default_settings as $setting) {
        $stmt->execute($setting);
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal inisialisasi tabel pengaturan: " . $e->getMessage();
}

// Handle form submission
if ($_POST && isset($_POST['save_settings'])) {
    try {
        $pdo->beginTransaction();
        
        foreach ($_POST as $key => $value) {
            if ($key === 'save_settings') continue;
            
            // Handle boolean values
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            
            $stmt = $pdo->prepare("UPDATE app_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        }
        
        $pdo->commit();
        $_SESSION['success'] = "Pengaturan berhasil disimpan";
        header('Location: index.php');
        exit();
        
    } catch (PDOException $e) {
        $pdo->rollback();
        $_SESSION['error'] = "Gagal menyimpan pengaturan: " . $e->getMessage();
    }
}

// Handle backup
if (isset($_POST['create_backup'])) {
    try {
        $backup_dir = '../../backups/';
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        $backup_file = $backup_dir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        // Simple backup using mysqldump alternative
        $tables = ['users', 'surat_masuk', 'surat_keluar', 'app_settings'];
        $backup_content = "-- E-Surat Backup " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tables as $table) {
            $stmt = $pdo->query("SELECT * FROM $table");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                $backup_content .= "-- Table: $table\n";
                $backup_content .= "DELETE FROM $table;\n";
                
                foreach ($rows as $row) {
                    $values = array_map(function($val) use ($pdo) {
                        return $pdo->quote($val);
                    }, array_values($row));
                    
                    $backup_content .= "INSERT INTO $table VALUES (" . implode(',', $values) . ");\n";
                }
                $backup_content .= "\n";
            }
        }
        
        file_put_contents($backup_file, $backup_content);
        $_SESSION['success'] = "Backup berhasil dibuat: " . basename($backup_file);
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Gagal membuat backup: " . $e->getMessage();
    }
}

// Get current settings
try {
    $stmt = $pdo->query("SELECT * FROM app_settings ORDER BY setting_group, setting_key");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_group']][] = $row;
    }
    
    // Get system information
    $system_info = [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'mysql_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'memory_limit' => ini_get('memory_limit'),
        'disk_free_space' => disk_free_space('.') ? round(disk_free_space('.') / 1024 / 1024 / 1024, 2) . ' GB' : 'Unknown'
    ];
    
    // Get database statistics
    $db_stats = [];
    $tables = ['users', 'surat_masuk', 'surat_keluar'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $db_stats[$table] = $stmt->fetch()['count'];
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal memuat pengaturan: " . $e->getMessage();
    $settings = [];
    $system_info = [];
    $db_stats = [];
}

$setting_groups = [
    'general' => ['title' => 'Umum', 'icon' => 'fas fa-cog'],
    'upload' => ['title' => 'Upload File', 'icon' => 'fas fa-upload'],
    'security' => ['title' => 'Keamanan', 'icon' => 'fas fa-shield-alt'],
    'system' => ['title' => 'Sistem', 'icon' => 'fas fa-server'],
    'appearance' => ['title' => 'Tampilan', 'icon' => 'fas fa-palette'],
    'display' => ['title' => 'Display', 'icon' => 'fas fa-desktop']
];
?>

<!-- Settings Navigation -->
<div class="settings-nav" style="margin-bottom: 2rem;">
    <div class="nav-tabs" style="display: flex; gap: 0.5rem; border-bottom: 2px solid #e5e7eb; flex-wrap: wrap;">
        <?php foreach ($setting_groups as $group_key => $group_info): ?>
        <button class="nav-tab" data-tab="<?= $group_key ?>" 
                style="padding: 1rem 1.5rem; border: none; background: transparent; color: #6b7280; font-weight: 500; cursor: pointer; border-bottom: 3px solid transparent; transition: all 0.3s ease;">
            <i class="<?= $group_info['icon'] ?>"></i>
            <span style="margin-left: 0.5rem;"><?= $group_info['title'] ?></span>
        </button>
        <?php endforeach; ?>
        
        <button class="nav-tab" data-tab="system-info" 
                style="padding: 1rem 1.5rem; border: none; background: transparent; color: #6b7280; font-weight: 500; cursor: pointer; border-bottom: 3px solid transparent; transition: all 0.3s ease;">
            <i class="fas fa-info-circle"></i>
            <span style="margin-left: 0.5rem;">Info Sistem</span>
        </button>
    </div>
</div>

<form method="POST" class="settings-form">
    
    <!-- Settings Sections -->
    <?php foreach ($setting_groups as $group_key => $group_info): ?>
    <div class="settings-section" id="section-<?= $group_key ?>" style="display: none;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="<?= $group_info['icon'] ?>"></i>
                    <?= $group_info['title'] ?>
                </h3>
                <p style="margin: 0.5rem 0 0; color: #6b7280; font-size: 0.9rem;">
                    Konfigurasi pengaturan <?= strtolower($group_info['title']) ?>
                </p>
            </div>
            <div class="card-body">
                <?php if (isset($settings[$group_key])): ?>
                <div class="settings-grid" style="display: grid; gap: 1.5rem;">
                    <?php foreach ($settings[$group_key] as $setting): ?>
                    <div class="setting-item" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; padding: 1.5rem; background: #f8fafc; border-radius: 0.75rem; border: 1px solid #e5e7eb;">
                        <div class="setting-info">
                            <label class="setting-label" style="display: block; font-weight: 600; color: var(--dark-color); margin-bottom: 0.5rem;">
                                <?= escape($setting['description']) ?>
                            </label>
                            <p style="margin: 0; color: #6b7280; font-size: 0.85rem; line-height: 1.4;">
                                Key: <code style="background: #e5e7eb; padding: 0.2rem 0.4rem; border-radius: 0.25rem; font-size: 0.8rem;"><?= escape($setting['setting_key']) ?></code>
                            </p>
                        </div>
                        
                        <div class="setting-control">
                            <?php if ($setting['setting_type'] === 'boolean'): ?>
                            <div class="toggle-switch" style="position: relative;">
                                <input type="hidden" name="<?= escape($setting['setting_key']) ?>" value="0">
                                <input type="checkbox" name="<?= escape($setting['setting_key']) ?>" value="1" 
                                       id="<?= escape($setting['setting_key']) ?>" 
                                       <?= $setting['setting_value'] == '1' ? 'checked' : '' ?>
                                       style="opacity: 0; position: absolute;">
                                <label for="<?= escape($setting['setting_key']) ?>" 
                                       style="display: block; width: 60px; height: 30px; background: #e5e7eb; border-radius: 15px; position: relative; cursor: pointer; transition: background 0.3s ease;">
                                    <span style="position: absolute; top: 3px; left: 3px; width: 24px; height: 24px; background: white; border-radius: 50%; transition: transform 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></span>
                                </label>
                            </div>
                            
                            <?php elseif ($setting['setting_type'] === 'textarea'): ?>
                            <textarea name="<?= escape($setting['setting_key']) ?>" class="form-control" rows="3"><?= escape($setting['setting_value']) ?></textarea>
                            
                            <?php elseif ($setting['setting_type'] === 'select'): ?>
                            <select name="<?= escape($setting['setting_key']) ?>" class="form-control form-select">
                                <?php
                                $options = [];
                                switch ($setting['setting_key']) {
                                    case 'theme_color':
                                        $options = ['blue' => 'Biru', 'green' => 'Hijau', 'purple' => 'Ungu', 'red' => 'Merah'];
                                        break;
                                    case 'date_format':
                                        $options = ['dd/mm/yyyy' => 'DD/MM/YYYY', 'mm/dd/yyyy' => 'MM/DD/YYYY', 'yyyy-mm-dd' => 'YYYY-MM-DD'];
                                        break;
                                    case 'timezone':
                                        $options = [
                                            'Asia/Jakarta' => 'WIB (Jakarta)',
                                            'Asia/Makassar' => 'WITA (Makassar)',
                                            'Asia/Jayapura' => 'WIT (Jayapura)'
                                        ];
                                        break;
                                }
                                ?>
                                <?php foreach ($options as $value => $label): ?>
                                <option value="<?= escape($value) ?>" <?= $setting['setting_value'] === $value ? 'selected' : '' ?>>
                                    <?= escape($label) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <?php else: ?>
                            <input type="<?= $setting['setting_type'] === 'number' ? 'number' : 'text' ?>" 
                                   name="<?= escape($setting['setting_key']) ?>" 
                                   class="form-control" 
                                   value="<?= escape($setting['setting_value']) ?>"
                                   <?= $setting['setting_type'] === 'number' ? 'min="0"' : '' ?>>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state" style="text-align: center; padding: 3rem; color: #6b7280;">
                    <i class="<?= $group_info['icon'] ?>" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                    <p>Tidak ada pengaturan untuk kategori ini</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <!-- System Info Section -->
    <div class="settings-section" id="section-system-info" style="display: none;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            
            <!-- System Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-server"></i>
                        Informasi Server
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-list">
                        <?php foreach ($system_info as $key => $value): ?>
                        <div class="info-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9;">
                            <span style="color: #6b7280; font-weight: 500;">
                                <?= ucwords(str_replace('_', ' ', $key)) ?>
                            </span>
                            <strong style="color: var(--dark-color);">
                                <?= escape($value) ?>
                            </strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Database Statistics -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-database"></i>
                        Statistik Database
                    </h3>
                </div>
                <div class="card-body">
                    <div class="stats-list">
                        <?php foreach ($db_stats as $table => $count): ?>
                        <div class="stat-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9;">
                            <span style="color: #6b7280; font-weight: 500;">
                                <i class="fas fa-table" style="margin-right: 0.5rem;"></i>
                                <?= ucwords(str_replace('_', ' ', $table)) ?>
                            </span>
                            <strong style="color: var(--primary-color);">
                                <?= number_format($count) ?> record<?= $count != 1 ? 's' : '' ?>
                            </strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Backup and Maintenance -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tools"></i>
                    Pemeliharaan Sistem
                </h3>
                <p style="margin: 0.5rem 0 0; color: #6b7280; font-size: 0.9rem;">
                    Alat untuk pemeliharaan dan backup database
                </p>
            </div>
            <div class="card-body">
                <div class="maintenance-actions" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                    
                    <!-- Database Backup -->
                    <div class="action-card" style="padding: 1.5rem; background: linear-gradient(135deg, #f0f9ff 0%, white 100%); border: 2px solid #e0f2fe; border-radius: 0.75rem;">
                        <div class="action-icon" style="width: 60px; height: 60px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                            <i class="fas fa-download" style="font-size: 1.5rem;"></i>
                        </div>
                        <h4 style="margin: 0 0 0.5rem; font-size: 1.1rem; font-weight: 600; color: var(--dark-color);">
                            Backup Database
                        </h4>
                        <p style="margin: 0 0 1rem; color: #6b7280; font-size: 0.9rem; line-height: 1.4;">
                            Buat backup database untuk mencegah kehilangan data
                        </p>
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="create_backup" class="btn btn-primary">
                                <i class="fas fa-download"></i>
                                Buat Backup
                            </button>
                        </form>
                    </div>
                    
                    <!-- Database Optimize -->
                    <div class="action-card" style="padding: 1.5rem; background: linear-gradient(135deg, #f0fdf4 0%, white 100%); border: 2px solid #dcfce7; border-radius: 0.75rem;">
                        <div class="action-icon" style="width: 60px; height: 60px; background: var(--success-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                            <i class="fas fa-tachometer-alt" style="font-size: 1.5rem;"></i>
                        </div>
                        <h4 style="margin: 0 0 0.5rem; font-size: 1.1rem; font-weight: 600; color: var(--dark-color);">
                            Optimasi Database
                        </h4>
                        <p style="margin: 0 0 1rem; color: #6b7280; font-size: 0.9rem; line-height: 1.4;">
                            Optimasi tabel database untuk performa yang lebih baik
                        </p>
                        <button type="button" onclick="optimizeDatabase()" class="btn btn-success">
                            <i class="fas fa-tachometer-alt"></i>
                            Optimasi Sekarang
                        </button>
                    </div>
                    
                    <!-- System Check -->
                    <div class="action-card" style="padding: 1.5rem; background: linear-gradient(135deg, #fffbeb 0%, white 100%); border: 2px solid #fef3c7; border-radius: 0.75rem;">
                        <div class="action-icon" style="width: 60px; height: 60px; background: var(--warning-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                            <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
                        </div>
                        <h4 style="margin: 0 0 0.5rem; font-size: 1.1rem; font-weight: 600; color: var(--dark-color);">
                            Pemeriksaan Sistem
                        </h4>
                        <p style="margin: 0 0 1rem; color: #6b7280; font-size: 0.9rem; line-height: 1.4;">
                            Periksa kesehatan sistem dan konfigurasi
                        </p>
                        <button type="button" onclick="systemCheck()" class="btn btn-warning">
                            <i class="fas fa-check-circle"></i>
                            Periksa Sistem
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Save Button -->
    <div class="settings-footer" style="position: sticky; bottom: 0; background: white; padding: 1.5rem; border-top: 2px solid #e5e7eb; margin-top: 2rem; border-radius: 0.75rem; box-shadow: 0 -4px 6px rgba(0, 0, 0, 0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div class="save-info" style="color: #6b7280; font-size: 0.9rem;">
                <i class="fas fa-info-circle"></i>
                Perubahan akan disimpan ke database
            </div>
            
            <div class="save-actions" style="display: flex; gap: 1rem;">
                <button type="button" onclick="resetForm()" class="btn btn-secondary">
                    <i class="fas fa-undo"></i>
                    Reset
                </button>
                
                <button type="submit" name="save_settings" class="btn btn-success">
                    <i class="fas fa-save"></i>
                    Simpan Pengaturan
                </button>
            </div>
        </div>
    </div>
</form>

<!-- System Check Modal -->
<div id="systemCheckModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">Pemeriksaan Sistem</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="systemCheckResults">
                <div class="loading-spinner" style="text-align: center; padding: 2rem;">
                    <div class="spinner"></div>
                    <p>Memeriksa sistem...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Tab navigation
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.nav-tab');
    const sections = document.querySelectorAll('.settings-section');
    
    // Show first tab by default
    if (tabs.length > 0) {
        showTab('general');
    }
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            showTab(targetTab);
        });
    });
    
    function showTab(tabName) {
        // Hide all sections
        sections.forEach(section => {
            section.style.display = 'none';
        });
        
        // Remove active class from all tabs
        tabs.forEach(tab => {
            tab.style.color = '#6b7280';
            tab.style.borderBottomColor = 'transparent';
        });
        
        // Show target section
        const targetSection = document.getElementById('section-' + tabName);
        if (targetSection) {
            targetSection.style.display = 'block';
        }
        
        // Activate target tab
        const activeTab = document.querySelector(`[data-tab="${tabName}"]`);
        if (activeTab) {
            activeTab.style.color = 'var(--primary-color)';
            activeTab.style.borderBottomColor = 'var(--primary-color)';
        }
    }
});

// Toggle switch functionality
document.addEventListener('change', function(e) {
    if (e.target.type === 'checkbox' && e.target.nextElementSibling && e.target.nextElementSibling.tagName === 'LABEL') {
        const label = e.target.nextElementSibling;
        const slider = label.querySelector('span');
        
        if (e.target.checked) {
            label.style.background = 'var(--success-color)';
            slider.style.transform = 'translateX(30px)';
        } else {
            label.style.background = '#e5e7eb';
            slider.style.transform = 'translateX(0)';
        }
    }
});

// Initialize toggle switches
document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
    if (checkbox.nextElementSibling && checkbox.nextElementSibling.tagName === 'LABEL') {
        const label = checkbox.nextElementSibling;
        const slider = label.querySelector('span');
        
        if (checkbox.checked) {
            label.style.background = 'var(--success-color)';
            slider.style.transform = 'translateX(30px)';
        }
    }
});

// Reset form
function resetForm() {
    if (confirm('Yakin ingin mereset semua perubahan?')) {
        location.reload();
    }
}

// Database optimization
function optimizeDatabase() {
    if (!confirm('Yakin ingin melakukan optimasi database? Proses ini mungkin memakan waktu beberapa menit.')) {
        return;
    }
    
    showLoading('Mengoptimasi database...');
    
    fetch('optimize_db.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            AppUtils.showAlert('Database berhasil dioptimasi', 'success');
        } else {
            AppUtils.showAlert('Gagal mengoptimasi database: ' + data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        AppUtils.showAlert('Terjadi kesalahan saat mengoptimasi database', 'error');
    });
}

// System check
function systemCheck() {
    const modal = document.getElementById('systemCheckModal');
    modal.classList.add('show');
    
    const resultsDiv = document.getElementById('systemCheckResults');
    resultsDiv.innerHTML = `
        <div class="loading-spinner" style="text-align: center; padding: 2rem;">
            <div class="spinner"></div>
            <p>Memeriksa sistem...</p>
        </div>
    `;
    
    fetch('system_check.php')
    .then(response => response.json())
    .then(data => {
        let html = '<div class="system-check-results">';
        
        data.checks.forEach(check => {
            const statusClass = check.status === 'OK' ? 'success' : check.status === 'WARNING' ? 'warning' : 'danger';
            const statusIcon = check.status === 'OK' ? 'fa-check-circle' : check.status === 'WARNING' ? 'fa-exclamation-triangle' : 'fa-times-circle';
            
            html += `
                <div class="check-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; margin-bottom: 0.5rem; background: #f8fafc; border-radius: 0.5rem; border-left: 4px solid var(--${statusClass}-color);">
                    <i class="fas ${statusIcon}" style="color: var(--${statusClass}-color); font-size: 1.2rem;"></i>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">${check.name}</div>
                        <div style="color: #6b7280; font-size: 0.9rem;">${check.message}</div>
                    </div>
                    <span class="status-badge status-${statusClass.toLowerCase()}" style="font-size: 0.8rem;">
                        ${check.status}
                    </span>
                </div>
            `;
        });
        
        html += '</div>';
        resultsDiv.innerHTML = html;
    })
    .catch(error => {
        resultsDiv.innerHTML = `
            <div class="error-message" style="text-align: center; padding: 2rem; color: var(--danger-color);">
                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p>Gagal melakukan pemeriksaan sistem</p>
            </div>
        `;
    });
}

// Form change detection
let formChanged = false;
document.querySelector('.settings-form').addEventListener('change', function() {
    formChanged = true;
});

// Warn before leaving if form changed
window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
        return 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
    }
});

// Reset form changed flag after save
document.querySelector('.settings-form').addEventListener('submit', function() {
    formChanged = false;
});

// Auto-save draft
const autoSave = AppUtils.debounce(function() {
    const formData = new FormData(document.querySelector('.settings-form'));
    const data = {};
    for (let [key, value] of formData.entries()) {
        if (key !== 'save_settings') {
            data[key] = value;
        }
    }
    localStorage.setItem('settings_draft', JSON.stringify(data));
}, 2000);

document.querySelector('.settings-form').addEventListener('input', autoSave);

// Restore draft on page load
window.addEventListener('load', function() {
    const draft = localStorage.getItem('settings_draft');
    if (draft) {
        try {
            const data = JSON.parse(draft);
            for (let [key, value] of Object.entries(data)) {
                const field = document.querySelector(`[name="${key}"]`);
                if (field) {
                    if (field.type === 'checkbox') {
                        field.checked = value === '1';
                        // Trigger change event for toggle switch styling
                        field.dispatchEvent(new Event('change'));
                    } else {
                        field.value = value;
                    }
                }
            }
        } catch (e) {
            console.warn('Failed to restore settings draft');
        }
    }
});

// Clear draft after successful save
<?php if (isset($_SESSION['success'])): ?>
localStorage.removeItem('settings_draft');
formChanged = false;
<?php endif; ?>
</script>

<style>
/* Settings specific styles */
.nav-tab:hover {
    color: var(--primary-color) !important;
    background: #f8fafc;
}

.nav-tab.active {
    color: var(--primary-color) !important;
    border-bottom-color: var(--primary-color) !important;
}

.toggle-switch input:checked + label {
    background: var(--success-color) !important;
}

.toggle-switch input:checked + label span {
    transform: translateX(30px) !important;
}

.action-card {
    transition: all 0.3s ease;
}

.action-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.settings-footer {
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

/* Modal styles */
.modal.show {
    display: flex;
}

.check-item {
    animation: slideInLeft 0.3s ease-out;
}

.check-item:nth-child(n) {
    animation-delay: calc(0.1s * var(--i));
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Responsive */
@media (max-width: 992px) {
    .setting-item {
        grid-template-columns: 1fr !important;
        gap: 1rem;
    }
    
    .maintenance-actions {
        grid-template-columns: 1fr !important;
    }
}

@media (max-width: 768px) {
    .nav-tabs {
        flex-direction: column;
    }
    
    .nav-tab {
        text-align: left;
        border-bottom: 1px solid #e5e7eb !important;
        border-radius: 0.5rem;
        margin-bottom: 0.25rem;
    }
    
    .settings-footer {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .save-actions {
        justify-content: center;
    }
}
</style>

<?php require_once '../../includes/footer.php'; ?>