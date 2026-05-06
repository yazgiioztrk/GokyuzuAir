<?php
// ticket.php — Bilet Sayfası
require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../services/flight_service.php';
$pageTitle = 'Biletiniz — Gökyüzü Air';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /GokyuzuAir/index.php'); exit; }

$ucus_id   = (int)($_POST['ucus_id'] ?? 0);
$ucus_json = $_POST['ucus_json'] ?? '';
$koltuk_no = trim($_POST['koltuk_no'] ?? '');
$ad        = trim($_POST['ad'] ?? '');
$soyad     = trim($_POST['soyad'] ?? '');
$email     = trim($_POST['email'] ?? '');
$tel       = trim($_POST['tel'] ?? '');
$cinsiyet  = $_POST['cinsiyet'] ?? 'Erkek';
$fiyat     = (float)($_POST['fiyat'] ?? 0);
$sinif     = $_POST['sinif'] ?? 'Ekonomi';
$gidis     = $_POST['gidis_tarihi'] ?? date('Y-m-d');
$donus     = $_POST['donus_tarihi'] ?? '';

$ucus = null;
if ($ucus_id > 0) $ucus = getFlightById($ucus_id);
if (!$ucus && $ucus_json) $ucus = json_decode($ucus_json, true);
if (!$ucus) { header('Location: /GokyuzuAir/index.php'); exit; }

// Rezervasyon kaydet
$bilet_no = saveRezervasyon([
    'ucus_id'      => $ucus_id,
    'ucus_json'    => $ucus_json,
    'kullanici_id' => isLoggedIn() ? getUser()['id'] : null,
    'koltuk_no'    => $koltuk_no,
    'ad'           => $ad, 'soyad' => $soyad,
    'email'        => $email, 'tel'  => $tel,
    'cinsiyet'     => $cinsiyet, 'sinif' => $sinif,
    'fiyat'        => $fiyat,
    'gidis_tarihi' => $gidis,
    'donus_tarihi' => $donus,
]);

// Bagaj limiti sınıfa göre
$bagaj_info = [
    'Ekonomi'     => ['cabin'=>'8 kg',  'checked'=>'20 kg', 'extra'=>'İlave bagaj ücrete tabidir.'],
    'Business'    => ['cabin'=>'12 kg', 'checked'=>'30 kg', 'extra'=>'1 adet ek bagaj ücretsizdir.'],
    'First Class' => ['cabin'=>'15 kg', 'checked'=>'40 kg', 'extra'=>'2 adet ek bagaj ücretsizdir.'],
];
$bagaj = $bagaj_info[$sinif] ?? $bagaj_info['Ekonomi'];

$kalkis_ts  = strtotime($ucus['kalkis_zamani'] ?? '00:00');
$kapi_kapat = date('H:i', $kalkis_ts - 30*60);
$hava_2saat = date('H:i', $kalkis_ts - 120*60);

// ──────────────────────────────────────────────────────────────
// QR KOD — tüm bilet bilgisi doğrudan metin olarak içine yazılır.
// Telefon localhost'a erişemeyeceği için URL değil, düz metin kullanıyoruz.
// Okuyan kişi bilgileri doğrudan görür.
// ──────────────────────────────────────────────────────────────
// QR içeriği — sade düz metin, hiçbir protokol yok
// Telefon sadece metni gösterir, arama veya rehber açmaz
$qr_icerik =
    "GOKYUZU AIR - BOARDING PASS\n" .
    "============================\n" .
    "Bilet : " . $bilet_no . "\n" .
    "Yolcu : " . strtoupper($ad . ' ' . $soyad) . "\n" .
    "Guzergah: " . ($ucus['kalkis_sehir'] ?? '') . " -> " . ($ucus['varis_sehir'] ?? '') . "\n" .
    "Ucus  : " . ($ucus['ucus_no'] ?? '') . "\n" .
    "Tarih : " . date('d.m.Y', strtotime($gidis)) . "\n" .
    "Kalkis: " . substr($ucus['kalkis_zamani'] ?? '00:00', 0, 5) . "\n" .
    "Varis : " . substr($ucus['varis_zamani']  ?? '00:00', 0, 5) . "\n" .
    "Koltuk: " . $koltuk_no . "\n" .
    "Sinif : " . $sinif . "\n" .
    "Firma : " . ($ucus['firma'] ?? '') . "\n" .
    "============================\n" .
    "Iyi yolculuklar!";

$qr_url = 'https://api.qrserver.com/v1/create-qr-code/'
        . '?size=220x220&margin=12&color=0a1535&bgcolor=ffffff'
        . '&data=' . urlencode($qr_icerik);

require_once __DIR__ . '/../ui/header.php';
?>

<div class="light-page">
<div class="page-wrapper">

  <div class="steps">
    <div class="step-item"><div class="step-dot done">✓</div><span class="step-label">Uçuş Seç</span></div>
    <div class="step-line done"></div>
    <div class="step-item"><div class="step-dot done">✓</div><span class="step-label">Koltuk</span></div>
    <div class="step-line done"></div>
    <div class="step-item"><div class="step-dot done">✓</div><span class="step-label">Yolcu</span></div>
    <div class="step-line done"></div>
    <div class="step-item"><div class="step-dot done">✓</div><span class="step-label">Ödeme</span></div>
    <div class="step-line done"></div>
    <div class="step-item"><div class="step-dot done">✓</div><span class="step-label">Bilet</span></div>
  </div>

  <div style="text-align:center;margin-bottom:2rem;">
    <div style="font-size:3rem;">🎉</div>
    <h1 class="page-title" style="text-align:center;color:#0a1535;">Rezervasyonunuz Tamamlandı!</h1>
    <p style="color:#666;font-size:0.95rem;">Biletiniz <strong style="color:#e8a020;"><?= htmlspecialchars($email) ?></strong> adresine gönderildi.</p>
  </div>

  <!-- BİLET -->
  <div class="ticket-wrapper" id="ticket">
    <div class="ticket-header">
      <div class="ticket-logo">Gökyüzü<em>Air</em>
        <span style="font-size:0.7rem;color:rgba(255,255,255,0.45);font-family:'DM Sans',sans-serif;display:block;margin-top:2px;">BOARDING PASS</span>
      </div>
      <div class="ticket-no">BİLET NO<strong><?= htmlspecialchars($bilet_no) ?></strong></div>
    </div>

    <div class="ticket-body">
      <div class="ticket-route">
        <div class="ticket-city">
          <div class="ticket-city-code"><?= strtoupper(mb_substr($ucus['kalkis_sehir']??'IST',0,3)) ?></div>
          <div class="ticket-city-name"><?= htmlspecialchars($ucus['kalkis_sehir']) ?></div>
          <div style="font-size:0.75rem;color:#888;"><?= substr($ucus['kalkis_zamani']??'00:00',0,5) ?></div>
        </div>
        <div class="ticket-route-mid">
          <div class="ticket-route-line"></div>
          <div class="ticket-duration"><?= htmlspecialchars($ucus['sure']??'') ?></div>
          <div style="font-size:0.7rem;color:#aaa;margin-top:2px;"><?= htmlspecialchars($ucus['ucak_tipi']??'') ?></div>
        </div>
        <div class="ticket-city ticket-varis">
          <div class="ticket-city-code"><?= strtoupper(mb_substr($ucus['varis_sehir']??'ANK',0,3)) ?></div>
          <div class="ticket-city-name"><?= htmlspecialchars($ucus['varis_sehir']) ?></div>
          <div style="font-size:0.75rem;color:#888;"><?= substr($ucus['varis_zamani']??'00:00',0,5) ?></div>
        </div>
      </div>

      <hr class="ticket-divider">

      <div class="ticket-info-grid">
        <div class="ticket-info-item"><label>Yolcu</label><span><?= htmlspecialchars(strtoupper($ad.' '.$soyad)) ?></span></div>
        <div class="ticket-info-item"><label>Uçuş No</label><span><?= htmlspecialchars($ucus['ucus_no']??'') ?></span></div>
        <div class="ticket-info-item"><label>Sınıf</label><span><?= htmlspecialchars($sinif) ?></span></div>
        <div class="ticket-info-item"><label>Tarih</label><span><?= date('d.m.Y', strtotime($gidis)) ?></span></div>
        <div class="ticket-info-item"><label>Kapı Kapanış</label><span><?= $kapi_kapat ?></span></div>
        <div class="ticket-info-item"><label>Havalimanı</label><span style="font-size:0.78rem;"><?= htmlspecialchars(substr($ucus['kalkis_havaalani']??'',0,22)) ?></span></div>
      </div>

      <div style="margin-top:1.5rem;display:flex;align-items:center;justify-content:space-between;">
        <div>
          <label style="font-size:0.7rem;color:#999;text-transform:uppercase;letter-spacing:1px;">Koltuk</label>
          <div class="ticket-seat-badge" style="margin-top:4px;"><?= htmlspecialchars($koltuk_no) ?></div>
        </div>
        <div style="text-align:right;">
          <label style="font-size:0.7rem;color:#999;text-transform:uppercase;letter-spacing:1px;">Toplam Ücret</label>
          <div style="font-size:1.6rem;font-weight:700;color:#0a1535;">₺<?= number_format($fiyat,0,'.','.') ?></div>
        </div>
      </div>

      <?php if ($donus): ?>
      <div style="margin-top:1rem;padding:0.8rem;background:#fff3cd;border-radius:8px;font-size:0.82rem;color:#856404;">
        🔄 Dönüş Uçuşu: <?= date('d.m.Y', strtotime($donus)) ?> · Aynı güzergah
      </div>
      <?php endif; ?>
    </div>

    <!-- QR BÖLÜMÜ -->
    <div class="ticket-qr-section">
      <div class="ticket-qr-left">
        <div class="qr-label">TARAMA KODU</div>
        <div class="qr-sub">Telefonunuzla okutunuz</div>
        <div style="font-family:monospace;font-size:0.6rem;color:#aaa;margin-top:6px;letter-spacing:1px;"><?= htmlspecialchars($bilet_no) ?></div>
        <div style="margin-top:10px;font-size:0.72rem;color:#777;line-height:1.5;">
          📱 QR kodu okutunca<br>
          bilet bilgileriniz<br>
          ekrana gelir
        </div>
      </div>
      <div class="ticket-qr-img">
        <img src="<?= htmlspecialchars($qr_url) ?>"
             alt="QR Kod"
             width="140" height="140"
             style="border:4px solid #f0e0b0;border-radius:10px;display:block;">
      </div>
    </div>
  </div>

  <!-- BİLGİLENDİRME PANELİ -->
  <div class="info-panel">
    <h3 class="info-panel-title">✈ Uçuşunuz Hakkında Önemli Bilgiler</h3>
    <div class="info-grid">

      <div class="info-card info-card-warning">
        <div class="info-icon">⏰</div>
        <div class="info-content">
          <strong>Havalimanında Olun</strong>
          <p>Kalkıştan en az <em>2 saat önce</em> havalimanında olmanız gerekmektedir.</p>
          <div class="info-highlight">En geç saat <?= $hava_2saat ?>'de burada olun</div>
        </div>
      </div>

      <div class="info-card info-card-blue">
        <div class="info-icon">🚪</div>
        <div class="info-content">
          <strong>Kapı Kapanış Saati</strong>
          <p>Uçuş kapıları kalkıştan <em>30 dakika önce</em> kapanır. Geç kalan yolcular alınamaz.</p>
          <div class="info-highlight">Kapı kapanış: <?= $kapi_kapat ?></div>
        </div>
      </div>

      <div class="info-card info-card-green">
        <div class="info-icon">🧳</div>
        <div class="info-content">
          <strong>Bagaj Hakkınız — <?= htmlspecialchars($sinif) ?></strong>
          <p>Kabin bagajı: <em><?= $bagaj['cabin'] ?></em><br>
             Kayıt bagajı: <em><?= $bagaj['checked'] ?></em></p>
          <div class="info-highlight"><?= $bagaj['extra'] ?></div>
        </div>
      </div>

      <div class="info-card info-card-purple">
        <div class="info-icon">🪪</div>
        <div class="info-content">
          <strong>Kimlik Belgesi</strong>
          <p>Yurt içi uçuşlarda <em>TC Kimlik Kartı</em> veya pasaport zorunludur.</p>
          <div class="info-highlight">Çocuklar için nüfus cüzdanı yeterlidir.</div>
        </div>
      </div>

      <div class="info-card info-card-orange">
        <div class="info-icon">🚫</div>
        <div class="info-content">
          <strong>Yasak Maddeler</strong>
          <p>Kabin bagajında sıvılar <em>100 ml</em> ile sınırlıdır. Kesici aletler yasaktır.</p>
          <div class="info-highlight">Elektronikler güvenlikte çıkarılmalıdır.</div>
        </div>
      </div>

      <div class="info-card info-card-teal">
        <div class="info-icon">📲</div>
        <div class="info-content">
          <strong>Online Check-in</strong>
          <p>Kalkıştan <em>24 saat önce</em> başlar, <em>1 saat önce</em> kapanır.</p>
          <div class="info-highlight">Bu QR kodu kapıda görevliye gösterebilirsiniz.</div>
        </div>
      </div>

    </div>
  </div>

  <!-- BUTONLAR -->
  <div style="display:flex;gap:1rem;justify-content:center;margin-top:2rem;flex-wrap:wrap;">
    <button onclick="window.print()" class="btn-print">🖨 Yazdır / PDF Kaydet</button>
    <a href="index.php" class="btn-nav btn-primary" style="padding:12px 28px;font-size:1rem;">← Ana Sayfaya Dön</a>
  </div>

</div>
</div>

<?php require_once __DIR__ . '/../ui/footer.php'; ?>
