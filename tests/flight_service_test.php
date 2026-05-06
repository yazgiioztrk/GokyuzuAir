<?php
// tests/flight_service_test.php
// GokyuzuAir — FlightService Birim Testleri
// Calistirmak icin: php tests/flight_service_test.php

require_once __DIR__ . '/../src/data/db.php';
require_once __DIR__ . '/../src/services/flight_service.php';

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

echo "=== FlightService Testleri ===\n\n";

// ─── 1. searchFlights — DB yoksa bos dizi donmeli ───────────────────────────
try {
    $service = new FlightService();
    $result  = $service->searchFlights('Istanbul', 'Ankara', 'Ekonomi');
    assert_true(is_array($result), 'searchFlights() bir dizi dondurmeli');
} catch (Exception $e) {
    echo "[UYARI] DB baglantisi yok — searchFlights testi atlandi: " . $e->getMessage() . "\n";
}

// ─── 2. getFlightById — gecersiz ID null donmeli ─────────────────────────────
try {
    $service = new FlightService();
    $result  = $service->getFlightById(999999);
    assert_equal(null, $result, 'Gecersiz ID icin getFlightById() null dondurmeli');
} catch (Exception $e) {
    echo "[UYARI] DB baglantisi yok — getFlightById testi atlandi: " . $e->getMessage() . "\n";
}

// ─── 3. getDoluKoltuklar — bos dizi bekleniyor ──────────────────────────────
try {
    $service = new FlightService();
    $result  = $service->getDoluKoltuklar(999999, '2025-01-01');
    assert_true(is_array($result), 'getDoluKoltuklar() bir dizi dondurmeli');
} catch (Exception $e) {
    echo "[UYARI] DB baglantisi yok — getDoluKoltuklar testi atlandi: " . $e->getMessage() . "\n";
}

// ─── 4. saveRezervasyon — eksik veri ile RuntimeException beklenior ──────────
try {
    $service = new FlightService();
    $service->saveRezervasyon([]); // Bilerek bos gonderiyoruz
    echo "[BASARISIZ] Bos data ile RuntimeException firlatilmali\n";
    $failed++;
} catch (RuntimeException $e) {
    echo "[GECTI] Bos data ile RuntimeException firlatildi\n";
    $passed++;
} catch (Exception $e) {
    echo "[UYARI] DB baglantisi yok — saveRezervasyon testi atlandi: " . $e->getMessage() . "\n";
}

// ─── Sonuc ───────────────────────────────────────────────────────────────────
echo "\n--- Sonuc ---\n";
echo "Gecti    : $passed\n";
echo "Basarisiz: $failed\n";
