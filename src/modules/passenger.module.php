<?php
// passenger.php — Yolcu Bilgileri
require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../services/flight_service.php';
$pageTitle = 'Yolcu Bilgileri — Gökyüzü Air';

$ucus_id     = (int)($_GET['ucus_id'] ?? 0);
$gidis       = $_GET['gidis_tarihi'] ?? date('Y-m-d');
$donus       = $_GET['donus_tarihi'] ?? '';
$koltuk_no   = $_GET['koltuk_no'] ?? '';
$ucus_json   = $_GET['ucus_json'] ?? '';

$ucus = null;
if ($ucus_id > 0) $ucus = getFlightById($ucus_id);
if (!$ucus && $ucus_json) $ucus = json_decode($ucus_json, true);
if (!$ucus || !$koltuk_no) { header('Location: /GokyuzuAir/index.php'); exit; }

$user = getUser();

require_once __DIR__ . '/../ui/header.php';
?>
<div class="page-wrapper">
  <a href="javascript:history.back()" class="btn-back">← Koltuk Seçimine Dön</a>

  <div class="steps">
    <div class="step-item"><div class="step-dot done">✓</div><span class="step-label">Uçuş Seç</span></div>
    <div class="step-line done"></div>
    <div class="step-item"><div class="step-dot done">✓</div><span class="step-label">Koltuk</span></div>
    <div class="step-line done"></div>
    <div class="step-item"><div class="step-dot active">3</div><span class="step-label">Yolcu</span></div>
    <div class="step-line"></div>
    <div class="step-item"><div class="step-dot">4</div><span class="step-label">Ödeme</span></div>
    <div class="step-line"></div>
    <div class="step-item"><div class="step-dot">5</div><span class="step-label">Bilet</span></div>
  </div>

  <div class="flight-summary">
    <div>
      <div class="summary-route"><?= htmlspecialchars($ucus['kalkis_sehir']) ?> → <?= htmlspecialchars($ucus['varis_sehir']) ?></div>
      <div class="summary-meta">Koltuk: <strong style="color:var(--sky-accent)"><?= htmlspecialchars($koltuk_no) ?></strong> · <?= date('d.m.Y', strtotime($gidis)) ?> · <?= htmlspecialchars($ucus['sinif'] ?? '') ?></div>
    </div>
    <div class="summary-price">₺<?= number_format($ucus['fiyat'],0,'.','.') ?></div>
  </div>

  <h1 class="page-title">Yolcu Bilgileri</h1>
  <p class="page-subtitle">Biletiniz için gerekli bilgileri doldurun.</p>

  <form action="payment.php" method="POST">
    <input type="hidden" name="ucus_id"     value="<?= $ucus_id ?>">
    <input type="hidden" name="gidis_tarihi" value="<?= htmlspecialchars($gidis) ?>">
    <input type="hidden" name="donus_tarihi" value="<?= htmlspecialchars($donus) ?>">
    <input type="hidden" name="koltuk_no"   value="<?= htmlspecialchars($koltuk_no) ?>">
    <input type="hidden" name="ucus_json"   value="<?= htmlspecialchars($ucus_json) ?>">
    <input type="hidden" name="fiyat"       value="<?= $ucus['fiyat'] ?>">
    <input type="hidden" name="sinif"       value="<?= htmlspecialchars($ucus['sinif'] ?? 'Ekonomi') ?>">

    <div class="form-card">
      <h3>👤 Kişisel Bilgiler</h3>
      <div class="form-row">
        <div class="form-group-dark">
          <label>Ad</label>
          <input type="text" name="ad" placeholder="Adınız" required value="<?= htmlspecialchars($user['ad'] ?? '') ?>">
        </div>
        <div class="form-group-dark">
          <label>Soyad</label>
          <input type="text" name="soyad" placeholder="Soyadınız" required value="<?= htmlspecialchars($user['soyad'] ?? '') ?>">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group-dark">
          <label>E-posta</label>
          <input type="email" name="email" placeholder="eposta@ornek.com" required value="<?= htmlspecialchars($user['email'] ?? '') ?>">
        </div>
        <div class="form-group-dark">
          <label>Telefon</label>
          <input type="tel" name="tel" placeholder="05XX XXX XX XX" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group-dark">
          <label>Cinsiyet</label>
          <select name="cinsiyet">
            <option value="Erkek">Erkek</option>
            <option value="Kadın">Kadın</option>
            <option value="Diğer">Diğer</option>
          </select>
        </div>
        <div class="form-group-dark">
          <label>TC Kimlik / Pasaport No</label>
          <input type="text" name="tc_no" placeholder="XXXXXXXXXXX" required>
        </div>
      </div>
    </div>
    <button type="submit" class="btn-continue">Ödemeye Geç →</button>
  </form>
</div>
<?php require_once __DIR__ . '/../ui/footer.php'; ?>
