<?php
session_start();
require_once 'config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_POST) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && md5($password) === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['level'] = $user['level'];
                $_SESSION['jabatan'] = $user['jabatan'];
                
                header('Location: index.php');
                exit();
            } else {
                $error = 'Username atau password salah';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Surat PTUN Banjarmasin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #3b82f6;
            --accent-color: #fbbf24;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-color: #f9fafb;
            --white-color: #ffffff;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 2rem;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            width: 900px;
            max-width: 95vw;
            display: grid;
            grid-template-columns: 1fr 1fr;
            position: relative;
            z-index: 1;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-left {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255, 255, 255, 0.03) 10px,
                rgba(255, 255, 255, 0.03) 20px
            );
            animation: slide 20s linear infinite;
        }

        @keyframes slide {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .logo-container {
            position: relative;
            z-index: 2;
            margin-bottom: 2rem;
        }

        .logo {
            width: 120px;
            height: 120px;
            background: var(--white-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--primary-color);
            margin: 0 auto 1rem;
            box-shadow: var(--shadow-lg);
            animation: pulse 2s infinite;
            position: relative;
            overflow: hidden;
        }

        .logo::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.3), transparent);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { left: -100%; }
            50%, 100% { left: 100%; }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .welcome-text {
            position: relative;
            z-index: 2;
        }

        .welcome-text h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .welcome-text p {
            font-size: 1.2rem;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 0.5rem;
        }

        .welcome-text .subtitle {
            font-size: 1rem;
            opacity: 0.8;
            font-style: italic;
        }

        .login-right {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            color: var(--dark-color);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #6b7280;
            font-size: 1rem;
        }

        .login-form {
            width: 100%;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            position: relative;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            transform: translateY(-2px);
        }

        .form-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.1rem;
            z-index: 2;
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 1rem;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 20px rgba(30, 58, 138, 0.4);
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--danger-color);
            background: #fef2f2;
            color: #dc2626;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: slideInDown 0.3s ease;
        }

        @keyframes slideInDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .demo-accounts {
            margin-top: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 0.75rem;
            border: 2px solid #e2e8f0;
        }

        .demo-accounts h4 {
            color: var(--dark-color);
            margin-bottom: 1rem;
            text-align: center;
            font-size: 1rem;
        }

        .demo-account {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background: white;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .demo-account:hover {
            border-color: var(--primary-color);
            transform: translateX(5px);
            box-shadow: var(--shadow);
        }

        .demo-account:last-child {
            margin-bottom: 0;
        }

        .demo-account strong {
            color: var(--primary-color);
        }

        .loading {
            display: none;
        }

        .btn-login.loading {
            color: transparent;
        }

        .btn-login.loading .loading {
            display: inline-block;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
                width: 100%;
                max-width: 400px;
                margin: 1rem;
            }

            .login-left {
                padding: 2rem;
                order: 2;
            }

            .login-right {
                padding: 2rem;
                order: 1;
            }

            .welcome-text h1 {
                font-size: 2rem;
            }

            .logo {
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }
        }

        /* Footer */
        .login-footer {
            position: absolute;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side - Welcome -->
        <div class="login-left">
            <div class="logo-container">
                <div class="logo">
                    <i class="fas fa-balance-scale"></i>
                </div>
            </div>
            <div class="welcome-text">
                <h1>E-Surat</h1>
                <p>Sistem Informasi Surat Masuk dan Surat Keluar</p>
                <p class="subtitle">Pengadilan Tata Usaha Negara Banjarmasin</p>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-right">
            <div class="login-header">
                <h2>Selamat Datang</h2>
                <p>Silakan masuk untuk mengakses sistem</p>
            </div>

            <?php if ($error): ?>
            <div class="alert">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div class="form-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <input type="text" id="username" name="username" class="form-control" 
                           placeholder="Masukkan username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="form-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Masukkan password" required>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <span class="btn-text">Masuk Sistem</span>
                    <div class="loading"></div>
                </button>
            </form>

            <!-- Demo Accounts -->
            <div class="demo-accounts">
                <h4><i class="fas fa-info-circle"></i> Akun Demo</h4>
                <div class="demo-account" onclick="fillLogin('admin', 'admin123')">
                    <span><strong>admin</strong> / admin123</span>
                    <small>Administrator</small>
                </div>
                <div class="demo-account" onclick="fillLogin('operator1', 'operator123')">
                    <span><strong>operator1</strong> / operator123</span>
                    <small>Operator Surat</small>
                </div>
                <div class="demo-account" onclick="fillLogin('pimpinan1', 'pimpinan123')">
                    <span><strong>pimpinan1</strong> / pimpinan123</span>
                    <small>Pimpinan</small>
                </div>
            </div>
        </div>
    </div>

    <div class="login-footer">
        <p>&copy; 2024 PTUN Banjarmasin. Sistem E-Surat.</p>
    </div>

    <script>
        // Fill login form with demo account
        function fillLogin(username, password) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;
            
            // Add visual feedback
            const demoAccounts = document.querySelectorAll('.demo-account');
            demoAccounts.forEach(account => account.style.background = 'white');
            event.target.closest('.demo-account').style.background = '#e0f2fe';
        }

        // Form submission with loading animation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            loginBtn.classList.add('loading');
            loginBtn.disabled = true;
            
            // Small delay for better UX
            setTimeout(() => {
                // Form will submit naturally
            }, 500);
        });

        // Enhanced form interactions
        const formControls = document.querySelectorAll('.form-control');
        formControls.forEach(control => {
            control.addEventListener('focus', function() {
                this.parentNode.style.transform = 'translateY(-2px)';
            });
            
            control.addEventListener('blur', function() {
                this.parentNode.style.transform = 'translateY(0)';
            });
        });

        // Floating animation for background elements
        function createFloatingElement() {
            const element = document.createElement('div');
            element.style.cssText = `
                position: absolute;
                width: ${Math.random() * 100 + 50}px;
                height: ${Math.random() * 100 + 50}px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
                top: ${Math.random() * 100}vh;
                left: ${Math.random() * 100}vw;
                animation: floatUp ${Math.random() * 10 + 10}s linear infinite;
                pointer-events: none;
                z-index: 0;
            `;
            
            document.body.appendChild(element);
            
            setTimeout(() => {
                element.remove();
            }, 20000);
        }

        // Add floating elements periodically
        setInterval(createFloatingElement, 3000);

        // Add CSS animation for floating elements
        const style = document.createElement('style');
        style.textContent = `
            @keyframes floatUp {
                from {
                    transform: translateY(100vh) rotate(0deg);
                    opacity: 0;
                }
                10% {
                    opacity: 1;
                }
                90% {
                    opacity: 1;
                }
                to {
                    transform: translateY(-100px) rotate(360deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>