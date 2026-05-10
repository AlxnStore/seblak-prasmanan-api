<?php
require_once 'config.php';
require_once 'cors.php';
setCorsHeaders();

$status = ['api' => 'OK', 'php' => PHP_VERSION, 'time' => date('Y-m-d H:i:s'), 'database' => 'Belum dicek'];

try {
    $db = getDB();
    $row = $db->query('SELECT COUNT(*) as total FROM menu_items')->fetch();
    $status['database']   = 'Terhubung';
    $status['menu_count'] = (int) $row['total'];
} catch (Exception $e) {
    $status['database'] = 'Error: ' . $e->getMessage();
}

echo json_encode(['success' => true, 'status' => $status], JSON_PRETTY_PRINT);
