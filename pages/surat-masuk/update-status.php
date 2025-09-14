<?php
// pages/surat-masuk/update-status.php
session_start();
require_once '../../config/database.php';

// Set response header to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $surat_ids = $_POST['surat_ids'] ?? '';
    $status = $_POST['status'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    
    // Validation
    if (empty($surat_ids)) {
        throw new Exception('ID surat tidak valid');
    }
    
    if (empty($status)) {
        throw new Exception('Status harus dipilih');
    }
    
    $valid_statuses = ['Masuk', 'Diproses', 'Selesai'];
    if (!in_array($status, $valid_statuses)) {
        throw new Exception('Status tidak valid');
    }
    
    // Parse IDs
    $ids = explode(',', $surat_ids);
    $ids = array_filter(array_map('intval', $ids));
    
    if (empty($ids)) {
        throw new Exception('ID surat tidak valid');
    }
    
    $pdo->beginTransaction();
    
    // Update status
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $params = array_merge([$status], $ids);
    
    if (!empty($keterangan)) {
        $sql = "UPDATE surat_masuk SET status = ?, keterangan = CONCAT(IFNULL(keterangan, ''), '\n[" . date('Y-m-d H:i:s') . "] Status diubah ke $status: $keterangan'), updated_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders)";
        array_splice($params, 1, 0, $keterangan);
    } else {
        $sql = "UPDATE surat_masuk SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders)";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $updatedCount = $stmt->rowCount();
    
    if ($updatedCount === 0) {
        throw new Exception('Tidak ada data yang diupdate');
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "$updatedCount surat berhasil diupdate ke status: $status"
    ]);
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>