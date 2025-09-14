<?php
// config/database.php
// Konfigurasi database untuk E-Surat PTUN Banjarmasin

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'e_surat_ptun';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi untuk format tanggal Indonesia
function formatTanggalIndo($tanggal) {
    if (empty($tanggal) || $tanggal === '0000-00-00') {
        return '-';
    }
    
    $bulan = array(
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    );
    
    $pecah = explode('-', $tanggal);
    if (count($pecah) == 3) {
        return $pecah[2] . ' ' . $bulan[(int)$pecah[1]] . ' ' . $pecah[0];
    }
    return $tanggal;
}

// Fungsi untuk validasi session
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Fungsi untuk escape string
function escape($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Fungsi untuk generate nomor surat
function generateNomorSurat($type = 'masuk') {
    global $pdo;
    
    $year = date('Y');
    $month = date('m');
    $monthRoman = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    
    if ($type == 'masuk') {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM surat_masuk WHERE YEAR(created_at) = ?");
        $stmt->execute([$year]);
        $count = $stmt->fetch()['total'] + 1;
        
        return sprintf("%03d/SM/%s/%s", $count, $monthRoman[(int)$month], $year);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM surat_keluar WHERE YEAR(created_at) = ?");
        $stmt->execute([$year]);
        $count = $stmt->fetch()['total'] + 1;
        
        return sprintf("%03d/SK/%s/%s", $count, $monthRoman[(int)$month], $year);
    }
}

// Fungsi untuk generate nomor agenda
function generateNomorAgenda($type = 'masuk') {
    global $pdo;
    
    $year = date('Y');
    
    if ($type == 'masuk') {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM surat_masuk WHERE YEAR(created_at) = ?");
        $stmt->execute([$year]);
        $count = $stmt->fetch()['total'] + 1;
        
        return sprintf("AG%03d/%s", $count, $year);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM surat_keluar WHERE YEAR(created_at) = ?");
        $stmt->execute([$year]);
        $count = $stmt->fetch()['total'] + 1;
        
        return sprintf("AK%03d/%s", $count, $year);
    }
}

// Set timezone
date_default_timezone_set('Asia/Makassar');
?>