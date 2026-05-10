<?php
// ============================================================
//  config.php — Konfigurasi Database
// ============================================================

define('DB_HOST', 'sql311.infinityfree.com');
define('DB_USER', 'if0_41875973');
define('DB_PASS', 'seblak125698');
define('DB_NAME', 'if0_41875973_db_seblak');

function getDB(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode([
            'success' => false,
            'message' => 'Koneksi database gagal: ' . $conn->connect_error
        ]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
