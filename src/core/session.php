<?php
// src/core/session.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['kullanici_id']);
}

function getUser() {
    if (!isLoggedIn()) return null;
    return [
        'id'    => $_SESSION['kullanici_id'],
        'ad'    => $_SESSION['kullanici_ad'],
        'soyad' => $_SESSION['kullanici_soyad'],
        'email' => $_SESSION['kullanici_email'],
    ];
}

function logout() {
    session_destroy();
    header('Location: /GokyuzuAir/index.php');
    exit;
}
?>
