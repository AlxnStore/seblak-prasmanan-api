<?php
// ============================================================
//  config.php — Konfigurasi Database (Railway MySQL)
// ============================================================

// Railway otomatis inject variabel environment ini
// Kalau deploy di Railway, tidak perlu ubah apapun di sini.
// Kalau mau test lokal, ganti nilainya sesuai kredensial lokal.

define('DB_HOST', getenv('MYSQLHOST')     ?: 'viaduct.proxy.rlwy.net');
define('DB_PORT', getenv('MYSQLPORT')     ?: '29303');
define('DB_USER', getenv('MYSQLUSER')     ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: 'veValoYwdELomekFqtTEDpNzDVbWbQHh');          // isi password Railway kamu di sini untuk test lokal
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'railway');

function getDB(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int) DB_PORT);
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
