<?php
define('DB_HOST', getenv('MYSQLHOST')     ?: 'viaduct.proxy.rlwy.net');
define('DB_PORT', getenv('MYSQLPORT')     ?: '29303');
define('DB_USER', getenv('MYSQLUSER')     ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: 'veValoYwdELomekFqtTEDpNzDVbWbQHh');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'railway');

function getDB(): PDO {
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
    try {
        return new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => 'Koneksi gagal: ' . $e->getMessage()]));
    }
}
