<?php
// src/data/db.php - Veritabanı bağlantısı
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gokyuzu_db');

function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Veritabanı bağlantısı başarısız: ' . $conn->connect_error]));
    }
    return $conn;
}
?>
