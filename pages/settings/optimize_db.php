<?php
// pages/settings/optimize_db.php - Database Optimization
session_start();
require_once '../../config/database.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['level'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

header('Content-Type: application/json');

try {
    $optimized_tables = [];
    $total_space_saved = 0;
    
    // List of tables to optimize
    $tables = ['users', 'surat_masuk', 'surat_keluar', 'app_settings'];
    
    // Start optimization
    $pdo->beginTransaction();
    
    foreach ($tables as $table) {
        // Get table size before optimization
        $stmt = $pdo->prepare("
            SELECT 
                table_name,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
            FROM information_schema.tables 
            WHERE table_schema = DATABASE() AND table_name = ?
        ");
        $stmt->execute([$table]);
        $before = $stmt->fetch();
        
        if ($before) {
            $size_before = $before['size_mb'];
            
            // Optimize table
            $pdo->exec("OPTIMIZE TABLE `$table`");
            
            // Get table size after optimization
            $stmt->execute([$table]);
            $after = $stmt->fetch();
            $size_after = $after ? $after['size_mb'] : $size_before;
            
            $space_saved = $size_before - $size_after;
            $total_space_saved += $space_saved;
            
            $optimized_tables[] = [
                'table' => $table,
                'size_before' => $size_before,
                'size_after' => $size_after,
                'space_saved' => $space_saved
            ];
        }
    }
    
    // Analyze tables for better performance
    foreach ($tables as $table) {
        $pdo->exec("ANALYZE TABLE `$table`");
    }
    
    // Repair tables if needed
    foreach ($tables as $table) {
        $pdo->exec("REPAIR TABLE `$table`");
    }
    
    $pdo->commit();
    
    // Log optimization activity
    $log_message = "Database optimization completed. Tables optimized: " . implode(', ', $tables) . ". Space saved: {$total_space_saved} MB";
    error_log($log_message);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database berhasil dioptimasi',
        'details' => [
            'tables_optimized' => count($optimized_tables),
            'total_space_saved' => round($total_space_saved, 2),
            'tables' => $optimized_tables
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (PDOException $e) {
    $pdo->rollback();
    
    error_log("Database optimization failed: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengoptimasi database: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    error_log("Database optimization error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>