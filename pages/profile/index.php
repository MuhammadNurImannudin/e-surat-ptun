<?php
// pages/profile/index.php - User Profile Management
$page_title = "Profil Pengguna";
$page_description = "Kelola informasi profil dan keamanan akun Anda";
$breadcrumbs = [
    ['title' => 'Profil Saya']
];

require_once '../../includes/header.php';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $nama_lengkap = trim($_POST['nama_lengkap']);
        $jabatan = trim($_POST['jabatan']);
        $email = trim($_POST['email']);
        $telepon = trim($_POST['telepon']);
        $alamat = trim($_POST['alamat']);
        
        // Validation
        $errors = [];
        
        if (empty($nama_lengkap)) {
            $errors[] = "Nama lengkap wajib diisi";
        }
        
        if (empty($jabatan)) {
            $errors[] = "Jabatan wajib diisi";
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Format email tidak valid";
        }
        
        if (empty($errors)) {
            try {
                // Check if email column exists, if not add it
                $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'email'");
                if ($stmt->rowCount() == 0) {
                    $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(100) NULL");
                }
                
                // Check if telepon column exists, if not add it
                $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'telepon'");
                if ($stmt->rowCount() == 0) {
                    $pdo->exec("ALTER TABLE users ADD COLUMN telepon VARCHAR(20) NULL");
                }
                
                // Check if alamat column exists, if not add it
                $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'alamat'");
                if ($stmt->rowCount() == 0) {
                    $pdo->exec("ALTER TABLE users ADD COLUMN alamat TEXT NULL");
                }
                
                $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, jabatan = ?, email = ?, telepon = ?, alamat = ? WHERE id = ?");
                $stmt->execute([$nama_lengkap, $jabatan, $email, $telepon, $alamat, $_SESSION['user_id']]);
                
                // Update session
                $_SESSION['nama_lengkap'] = $nama_lengkap;
                $_SESSION['jabatan'] = $jabatan;
                
                $_SESSION['success'] = "Profil berhasil diperbarui";
                header('Location: index.php');
                exit();
                
            } catch (PDOException $e) {
                $_SESSION['error'] = "Gagal memperbarui profil: " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = implode('<br>', $errors);
        }
        
    } elseif (isset($_POST['change_password'])) {
        // Change password
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        $errors = [];
        
        if (empty($current_password)) {
            $errors[] = "Password lama wajib diisi";
        }
        
        if (empty($new_password)) {
            $errors[] = "Password baru wajib diisi";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "Password baru minimal 6 karakter";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "Konfirmasi password tidak cocok";
        }
        
        if (empty($errors)) {
            try {
                // Verify current password
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user_data = $stmt->fetch();
                
                if (md5($current_password) !== $user_data['password']) {
                    $errors[] = "Password lama tidak benar";
                } else {
                    // Update password
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([md5($new_password), $_SESSION['user_id']]);
                    
                    $_SESSION['success'] = "Password berhasil diubah";
                    header('Location: index.php');
                    exit();
                }
                
            } catch (PDOException $e) {
                $_SESSION['error'] = "Gagal mengubah password: " . $e->getMessage();
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
        }
    }
}

// Get updated user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_profile = $stmt->fetch();
    
    if (!$user_profile) {
        $_SESSION['error'] = "Data pengguna tidak ditemukan";
        header('Location: ../../index.php');
        exit();
    }
    
    // Get user activity stats
    $stmt = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM surat_masuk WHERE created_by = ?) as total_input_masuk,
            (SELECT COUNT(*) FROM surat_keluar WHERE created_by = ?) as total_input_keluar
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $activity_stats = $stmt->fetch();
    
    // Get recent activity
    $stmt = $pdo->prepare("
        (SELECT 'surat_masuk' as type, nomor_surat, perihal, created_at as activity_date 
         FROM surat_masuk WHERE created_by = ? ORDER BY created_at DESC LIMIT 5)
        UNION
        (SELECT 'surat_keluar' as type, nomor_surat, perihal, created_at as activity_date 
         FROM surat_keluar WHERE created_by = ? ORDER BY created_at DESC LIMIT 5)
        ORDER BY activity_date DESC LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $recent_activities = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal memuat data profil: " . $e->getMessage();
    $user_profile = $current_user;
    $activity_stats = ['total_input_masuk' => 0, 'total_input_keluar' => 0];
    $recent_activities = [];
}
?>

<div class="profile-container" style="display: grid; grid-template-columns: 300px 1fr; gap: 2rem;">
    
    <!-- Profile Sidebar -->
    <div class="profile-sidebar">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div class="profile-avatar" style="width: 120px; height: 120px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; font-weight: 700; box-shadow: var(--shadow-lg);">
                    <?= strtoupper(substr($user_profile['nama_lengkap'], 0, 1)) ?>
                </div>
                
                <h3 style="margin: 0 0 0.5rem; font-size: 1.3rem; font-weight: 600; color: var(--dark-color);">
                    <?= escape($user_profile['nama_lengkap']) ?>
                </h3>
                
                <p style="margin: 0 0 0.5rem; color: #6b7280; font-size: 0.95rem;">
                    <?= escape($user_profile['jabatan']) ?>
                </p>
                
                <span class="level-badge" style="display: inline-block; padding: 0.375rem 0.875rem; background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%); color: white; border-radius: 50px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                    <?= ucfirst($user_profile['level']) ?>
                </span>
                
                <div class="profile-stats" style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
                    <div class="stat-item" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <span style="color: #6b7280; font-size: 0.9rem;">
                            <i class="fas fa-inbox"></i>
                            Surat Masuk
                        </span>
                        <strong style="color: var(--primary-color);">
                            <?= number_format($activity_stats['total_input_masuk']) ?>
                        </strong>
                    </div>
                    
                    <div class="stat-item" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <span style="color: #6b7280; font-size: 0.9rem;">
                            <i class="fas fa-paper-plane"></i>
                            Surat Keluar
                        </span>
                        <strong style="color: var(--success-color);">
                            <?= number_format($activity_stats['total_input_keluar']) ?>
                        </strong>
                    </div>
                    
                    <div class="stat-item" style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #6b7280; font-size: 0.9rem;">
                            <i class="fas fa-calendar"></i>
                            Bergabung
                        </span>
                        <strong style="color: var(--warning-color); font-size: 0.85rem;">
                            <?= date('M Y', strtotime($user_profile['created_at'])) ?>
                        </strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card" style="margin-top: 1.5rem;">
            <div class="card-header">
                <h4 class="card-title" style="font-size: 1rem;">
                    <i class="fas fa-bolt"></i>
                    Aksi Cepat
                </h4>
            </div>
            <div class="card-body" style="padding: 1rem;">
                <div class="quick-actions">
                    <a href="../surat-masuk/tambah.php" class="quick-action-btn" style="display: block; padding: 0.75rem; margin-bottom: 0.5rem; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 0.5rem; text-decoration: none; color: var(--dark-color); transition: all 0.3s ease;">
                        <i class="fas fa-plus text-primary"></i>
                        <span style="margin-left: 0.5rem;">Tambah Surat Masuk</span>
                    </a>
                    
                    <a href="../surat-keluar/tambah.php" class="quick-action-btn" style="display: block; padding: 0.75rem; margin-bottom: 0.5rem; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 0.5rem; text-decoration: none; color: var(--dark-color); transition: all 0.3s ease;">
                        <i class="fas fa-edit text-success"></i>
                        <span style="margin-left: 0.5rem;">Buat Surat Keluar</span>
                    </a>
                    
                    <a href="../reports/report-rekap.php" class="quick-action-btn" style="display: block; padding: 0.75rem; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 0.5rem; text-decoration: none; color: var(--dark-color); transition: all 0.3s ease;">
                        <i class="fas fa-chart-bar text-warning"></i>
                        <span style="margin-left: 0.5rem;">Lihat Laporan</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Profile Content -->
    <div class="profile-content">
        
        <!-- Profile Information -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user"></i>
                    Informasi Profil
                </h3>
                <p style="margin: 0.5rem 0 0; color: #6b7280; font-size: 0.9rem;">
                    Perbarui informasi profil dan data kontak Anda
                </p>
            </div>
            <div class="card-body">
                <form method="POST" class="profile-form">
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label" for="nama_lengkap">Nama Lengkap <span style="color: var(--danger-color);">*</span></label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" 
                                   value="<?= escape($user_profile['nama_lengkap']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="jabatan">Jabatan <span style="color: var(--danger-color);">*</span></label>
                            <input type="text" id="jabatan" name="jabatan" class="form-control" 
                                   value="<?= escape($user_profile['jabatan']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label" for="username">Username</label>
                            <input type="text" id="username" class="form-control" 
                                   value="<?= escape($user_profile['username']) ?>" disabled>
                            <small style="color: #6b7280; font-size: 0.85rem;">Username tidak dapat diubah</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="level">Level Akses</label>
                            <input type="text" id="level" class="form-control" 
                                   value="<?= ucfirst($user_profile['level']) ?>" disabled>
                            <small style="color: #6b7280; font-size: 0.85rem;">Level akses diatur oleh administrator</small>
                        </div>
                    </div>
                    
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?= escape($user_profile['email'] ?? '') ?>" placeholder="nama@email.com">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="telepon">Nomor Telepon</label>
                            <input type="tel" id="telepon" name="telepon" class="form-control" 
                                   value="<?= escape($user_profile['telepon'] ?? '') ?>" placeholder="081234567890">
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label class="form-label" for="alamat">Alamat</label>
                        <textarea id="alamat" name="alamat" class="form-control" rows="3" 
                                  placeholder="Masukkan alamat lengkap..."><?= escape($user_profile['alamat'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-lock"></i>
                    Ubah Password
                </h3>
                <p style="margin: 0.5rem 0 0; color: #6b7280; font-size: 0.9rem;">
                    Pastikan password Anda kuat dan aman
                </p>
            </div>
            <div class="card-body">
                <form method="POST" class="password-form">
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label class="form-label" for="current_password">Password Lama <span style="color: var(--danger-color);">*</span></label>
                        <div style="position: relative;">
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('current_password')" 
                                    style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6b7280; cursor: pointer;">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label" for="new_password">Password Baru <span style="color: var(--danger-color);">*</span></label>
                            <div style="position: relative;">
                                <input type="password" id="new_password" name="new_password" class="form-control" 
                                       minlength="6" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('new_password')" 
                                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6b7280; cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small style="color: #6b7280; font-size: 0.85rem;">Minimal 6 karakter</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="confirm_password">Konfirmasi Password <span style="color: var(--danger-color);">*</span></label>
                            <div style="position: relative;">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                       minlength="6" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')" 
                                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6b7280; cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="password-strength" id="passwordStrength" style="margin-bottom: 1.5rem; display: none;">
                        <div class="strength-meter" style="height: 4px; background: #e5e7eb; border-radius: 2px; margin-bottom: 0.5rem;">
                            <div class="strength-fill" style="height: 100%; background: var(--danger-color); border-radius: 2px; transition: all 0.3s ease; width: 0%;"></div>
                        </div>
                        <div class="strength-text" style="font-size: 0.85rem; color: #6b7280;">
                            <span id="strengthText">Password lemah</span>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="change_password" class="btn btn-danger">
                            <i class="fas fa-key"></i>
                            Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Activity History -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history"></i>
                    Aktivitas Terbaru
                </h3>
                <p style="margin: 0.5rem 0 0; color: #6b7280; font-size: 0.9rem;">
                    10 aktivitas terakhir Anda dalam sistem
                </p>
            </div>
            <div class="card-body">
                <?php if (empty($recent_activities)): ?>
                    <div class="empty-state" style="text-align: center; padding: 2rem; color: #6b7280;">
                        <i class="fas fa-history" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                        <p>Belum ada aktivitas yang tercatat</p>
                    </div>
                <?php else: ?>
                    <div class="activity-timeline">
                        <?php foreach ($recent_activities as $index => $activity): ?>
                        <div class="activity-item" style="display: flex; align-items: start; gap: 1rem; padding: 1rem; margin-bottom: 0.5rem; background: #f8fafc; border-radius: 0.5rem; border-left: 4px solid <?= $activity['type'] == 'surat_masuk' ? 'var(--primary-color)' : 'var(--success-color)' ?>;">
                            <div class="activity-icon" style="width: 40px; height: 40px; background: <?= $activity['type'] == 'surat_masuk' ? 'var(--primary-color)' : 'var(--success-color)' ?>; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas fa-<?= $activity['type'] == 'surat_masuk' ? 'inbox' : 'paper-plane' ?>" style="font-size: 0.9rem;"></i>
                            </div>
                            <div class="activity-content" style="flex: 1;">
                                <div class="activity-title" style="font-weight: 600; color: var(--dark-color); margin-bottom: 0.25rem; font-size: 0.95rem;">
                                    Input <?= $activity['type'] == 'surat_masuk' ? 'Surat Masuk' : 'Surat Keluar' ?>
                                </div>
                                <div class="activity-description" style="color: #6b7280; font-size: 0.9rem; margin-bottom: 0.5rem;">
                                    <strong><?= escape($activity['nomor_surat']) ?></strong> - 
                                    <?= escape(strlen($activity['perihal']) > 50 ? substr($activity['perihal'], 0, 50) . '...' : $activity['perihal']) ?>
                                </div>
                                <div class="activity-time" style="color: #9ca3af; font-size: 0.8rem;">
                                    <i class="fas fa-clock"></i>
                                    <?= formatTanggalIndo(date('Y-m-d', strtotime($activity['activity_date']))) ?> 
                                    <?= date('H:i', strtotime($activity['activity_date'])) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="text-center" style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                        <a href="../reports/report-rekap.php" class="btn btn-outline-primary">
                            <i class="fas fa-chart-bar"></i>
                            Lihat Laporan Lengkap
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength checker
document.getElementById('new_password').addEventListener('input', function() {
    const password = this.value;
    const strengthDiv = document.getElementById('passwordStrength');
    const strengthFill = strengthDiv.querySelector('.strength-fill');
    const strengthText = document.getElementById('strengthText');
    
    if (password.length === 0) {
        strengthDiv.style.display = 'none';
        return;
    }
    
    strengthDiv.style.display = 'block';
    
    let strength = 0;
    let text = 'Password lemah';
    let color = 'var(--danger-color)';
    
    // Length check
    if (password.length >= 6) strength += 20;
    if (password.length >= 8) strength += 10;
    
    // Character variety checks
    if (/[a-z]/.test(password)) strength += 20;
    if (/[A-Z]/.test(password)) strength += 20;
    if (/[0-9]/.test(password)) strength += 20;
    if (/[^A-Za-z0-9]/.test(password)) strength += 10;
    
    if (strength <= 30) {
        text = 'Password lemah';
        color = 'var(--danger-color)';
    } else if (strength <= 60) {
        text = 'Password sedang';
        color = 'var(--warning-color)';
    } else if (strength <= 80) {
        text = 'Password kuat';
        color = 'var(--success-color)';
    } else {
        text = 'Password sangat kuat';
        color = 'var(--success-color)';
    }
    
    strengthFill.style.width = strength + '%';
    strengthFill.style.background = color;
    strengthText.textContent = text;
    strengthText.style.color = color;
});

// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && newPassword !== confirmPassword) {
        this.setCustomValidity('Password tidak cocok');
        this.style.borderColor = 'var(--danger-color)';
    } else {
        this.setCustomValidity('');
        this.style.borderColor = '';
    }
});

// Form validation
document.querySelector('.password-form').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        AppUtils.showAlert('Konfirmasi password tidak cocok', 'error');
        return false;
    }
    
    if (newPassword.length < 6) {
        e.preventDefault();
        AppUtils.showAlert('Password minimal 6 karakter', 'error');
        return false;
    }
});

// Quick action button hover effects
document.querySelectorAll('.quick-action-btn').forEach(btn => {
    btn.addEventListener('mouseenter', function() {
        this.style.background = '#e2e8f0';
        this.style.borderColor = 'var(--primary-color)';
        this.style.transform = 'translateX(5px)';
    });
    
    btn.addEventListener('mouseleave', function() {
        this.style.background = '#f8fafc';
        this.style.borderColor = '#e5e7eb';
        this.style.transform = 'translateX(0)';
    });
});

// Auto-save form data
const profileForm = document.querySelector('.profile-form');
if (profileForm) {
    const inputs = profileForm.querySelectorAll('input:not([disabled]), textarea');
    inputs.forEach(input => {
        input.addEventListener('input', AppUtils.debounce(() => {
            const formData = new FormData(profileForm);
            const data = {};
            for (let [key, value] of formData.entries()) {
                if (key !== 'update_profile') {
                    data[key] = value;
                }
            }
            localStorage.setItem('profile_draft', JSON.stringify(data));
        }, 1000));
    });
    
    // Restore draft on page load
    const savedDraft = localStorage.getItem('profile_draft');
    if (savedDraft) {
        try {
            const data = JSON.parse(savedDraft);
            for (let [key, value] of Object.entries(data)) {
                const field = profileForm.querySelector(`[name="${key}"]`);
                if (field && !field.disabled) {
                    field.value = value;
                }
            }
        } catch (e) {
            console.warn('Failed to restore profile draft');
        }
    }
}

// Clear draft after successful save
<?php if (isset($_SESSION['success'])): ?>
localStorage.removeItem('profile_draft');
<?php endif; ?>
</script>

<style>
/* Profile specific styles */
.profile-container {
    gap: 2rem;
}

@media (max-width: 992px) {
    .profile-container {
        grid-template-columns: 1fr;
    }
    
    .profile-sidebar {
        order: 2;
    }
    
    .profile-content {
        order: 1;
    }
}

.profile-avatar {
    position: relative;
    overflow: hidden;
}

.profile-avatar::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% { left: -100%; }
    50%, 100% { left: 100%; }
}

.level-badge {
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.activity-item {
    transition: all 0.3s ease;
}

.activity-item:hover {
    background: #f1f5f9 !important;
    transform: translateX(5px);
}

.password-toggle {
    transition: color 0.3s ease;
}

.password-toggle:hover {
    color: var(--primary-color) !important;
}

.form-control:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    border-color: var(--primary-color);
}

/* Custom scrollbar for activity list */
.activity-timeline {
    max-height: 400px;
    overflow-y: auto;
}

.activity-timeline::-webkit-scrollbar {
    width: 4px;
}

.activity-timeline::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.activity-timeline::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 2px;
}

/* Button outline style */
.btn-outline-primary {
    color: var(--primary-color);
    border: 1px solid var(--primary-color);
    background: transparent;
    transition: all 0.3s ease;
}

.btn-outline-primary:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
}

/* Form row responsive */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once '../../includes/footer.php'; ?>