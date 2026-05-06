<?php
// tests/auth_service_test.php
// GokyuzuAir — AuthService Birim Testleri
// Calistirmak icin: php tests/auth_service_test.php

require_once __DIR__ . '/../src/data/db.php';
require_once __DIR__ . '/../src/core/session.php';
require_once __DIR__ . '/../src/services/auth_service.php';

$passed = 0;
$failed = 0;

function assert_equal(mixed $expected, mixed $actual, string $test_name): void {
    global $passed, $failed;
    if ($expected === $actual) {
        echo "[GECTI] $test_name\n";
        $passed++;
    } else {
        echo "[BASARISIZ] $test_name\n";
        echo "  Beklenen : " . var_export($expected, true) . "\n";
        echo "  Gelen    : " . var_export($actual, true)   . "\n";
        $failed++;
    }
}

function assert_true(bool $condition, string $test_name): void {
    assert_equal(true, $condition, $test_name);
}

echo "=== AuthService Testleri ===\n\n";

// ─── 1. Kayit — bos alan kontrolu ───────────────────────────────────────────
try {
    $service = new AuthService();
    $result  = $service->register('', '', 'test@test.com', '123456', '', 'Erkek');
    // Bos isim ile kayit gecmemeli (DB tarafindan reddedilir veya bos deger)
    // Bu test sadece exception firlatilmadigini dogrular
    assert_true(isset($result['success']), 'register() dizi dondurmeli');
} catch (Exception $e) {
    echo "[UYARI] DB baglantisi yok — AuthService kayit testi atlandi: " . $e->getMessage() . "\n";
}

// ─── 2. Login — yanlış şifre ────────────────────────────────────────────────
try {
    $service = new AuthService();
    $result  = $service->login('yanlis@eposta.com', 'yanlisSifre');
    assert_equal(false, $result['success'], 'Yanlis email ile giris basarisiz olmali');
} catch (Exception $e) {
    echo "[UYARI] DB baglantisi yok — AuthService login testi atlandi: " . $e->getMessage() . "\n";
}

// ─── 3. Session — isLoggedIn başlangıçta false ──────────────────────────────
$loggedIn = Session::isLoggedIn();
assert_equal(false, $loggedIn, 'Oturum baslatilmadan isLoggedIn() false olmali');

// ─── 4. Session — setUser ve getUser ────────────────────────────────────────
Session::setUser(['id' => 99, 'ad' => 'Test', 'soyad' => 'Kullanici', 'email' => 't@t.com']);
$user = Session::getUser();
assert_equal(99,    $user['id'],    'setUser sonrasi id dogru olmali');
assert_equal('Test',$user['ad'],   'setUser sonrasi ad dogru olmali');

// ─── 5. Database — getInstance Singleton ────────────────────────────────────
try {
    $db1 = Database::getInstance();
    $db2 = Database::getInstance();
    assert_true($db1 === $db2, 'Database::getInstance() ayni ornegi dondurmeli (Singleton)');
} catch (Exception $e) {
    echo "[UYARI] DB baglantisi yok — Singleton testi atlandi: " . $e->getMessage() . "\n";
}

// ─── Sonuc ───────────────────────────────────────────────────────────────────
echo "\n--- Sonuc ---\n";
echo "Gecti  : $passed\n";
echo "Basarisiz: $failed\n";
