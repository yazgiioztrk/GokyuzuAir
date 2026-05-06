<?php
// src/services/auth_service.php
require_once __DIR__ . '/../data/db.php';
require_once __DIR__ . '/../core/session.php';

function registerUser($ad, $soyad, $email, $sifre, $tel, $cinsiyet) {
    $db = getDB();
    $email = $db->real_escape_string($email);
    $check = $db->query("SELECT id FROM kullanicilar WHERE email='$email'");
    if ($check->num_rows > 0) {
        return ['success' => false, 'message' => 'Bu e-posta adresi zaten kayıtlı.'];
    }
    $hash = password_hash($sifre, PASSWORD_DEFAULT);
    $ad = $db->real_escape_string($ad);
    $soyad = $db->real_escape_string($soyad);
    $tel = $db->real_escape_string($tel);
    $cinsiyet = $db->real_escape_string($cinsiyet);
    $db->query("INSERT INTO kullanicilar (ad,soyad,email,sifre,tel,cinsiyet) VALUES ('$ad','$soyad','$email','$hash','$tel','$cinsiyet')");
    return ['success' => true, 'message' => 'Kayıt başarılı! Giriş yapabilirsiniz.'];
}

function loginUser($email, $sifre) {
    $db = getDB();
    $email = $db->real_escape_string($email);
    $result = $db->query("SELECT * FROM kullanicilar WHERE email='$email'");
    if ($result->num_rows === 0) {
        return ['success' => false, 'message' => 'E-posta veya şifre hatalı.'];
    }
    $user = $result->fetch_assoc();
    if (!password_verify($sifre, $user['sifre'])) {
        return ['success' => false, 'message' => 'E-posta veya şifre hatalı.'];
    }
    $_SESSION['kullanici_id']    = $user['id'];
    $_SESSION['kullanici_ad']    = $user['ad'];
    $_SESSION['kullanici_soyad'] = $user['soyad'];
    $_SESSION['kullanici_email'] = $user['email'];
    return ['success' => true, 'message' => 'Giriş başarılı!'];
}
?>
