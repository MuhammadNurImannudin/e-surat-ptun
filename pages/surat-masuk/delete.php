<?php
// pages/surat-masuk/delete.php
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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    if (isset($input['id'])) {
        // Single delete
        $id = (int)$input['id'];
        
        // Check if record exists
        $stmt = $pdo->prepare("SELECT id, file_surat FROM surat_masuk WHERE id = ?");
        $stmt->execute([$id]);
        $surat = $stmt->fetch();
        
        if (!$surat) {
            throw new Exception('Surat masuk tidak ditemukan');
        }
        
        // Delete file if exists
        if ($surat['file_surat'] && file_exists("../../uploads/surat-masuk/" . $surat['file_surat'])) {
            unlink("../../uploads/surat-masuk/" . $surat['file_surat']);
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM surat_masuk WHERE id = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Surat masuk berhasil dihapus']);
        
    } elseif (isset($input['ids']) && is_array($input['ids'])) {
        // Bulk delete
        $ids = array_map('intval', $input['ids']);
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        
        // Get files to delete
        $stmt = $pdo->prepare("SELECT file_surat FROM surat_masuk WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $files = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Delete files
        foreach ($files as $file) {
            if ($file && file_exists("../../uploads/surat-masuk/" . $file)) {
                unlink("../../uploads/surat-masuk/" . $file);
            }
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM surat_masuk WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        
        $deletedCount = $stmt->rowCount();
        
        $pdo->commit();
        echo json_encode([
            'success' => true, 
            'message' => "$deletedCount surat masuk berhasil dihapus"
        ]);
        
    } else {
        throw new Exception('Parameter tidak valid');
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false, 
        'message' => 'Gagal menghapus data: ' . $e->getMessage()
    ]);
}
?>