<?php
// seats.php — Koltuk Seçimi
require_once __DIR__ . '/../services/flight_service.php';
require_once __DIR__ . '/../core/session.php';
$pageTitle = 'Koltuk Seçimi — Gökyüzü Air';

$ucus_id   = (int)($_GET['ucus_id'] ?? 0);
$gidis     = $_GET['gidis_tarihi'] ?? date('Y-m-d');
$donus     = $_GET['donus_tarihi'] ?? '';
$kalkis    = $_GET['kalkis'] ?? '';
$varis     = $_GET['varis'] ?? '';
$ucus_json = $_GET['ucus_json'] ?? '';

$ucus = null;
if ($ucus_id > 0) $ucus = getFlightById($ucus_id);
if (!$ucus && $ucus_json) $ucus = json_decode($ucus_json, true);
if (!$ucus) { header('Location: /GokyuzuAir/index.php'); exit; }

// Dolu koltuklar — DB'den al + demo doluluk ekle
$doluKoltuklar = [];
if ($ucus_id > 0 && $ucus_id < 1000) {
    $doluKoltuklar = getDoluKoltuklar($ucus_id, $gidis);
}
$seed = crc32(($ucus['ucus_no'] ?? 'X') . $gidis);
srand($seed);
for ($i = 0; $i < 22; $i++) {
    $row = rand(1, 30);
    $col = ['A','B','C','D','E','F'][rand(0,5)];
    $doluKoltuklar[] = "$row$col";
}
$doluKoltuklar = array_values(array_unique($doluKoltuklar));

require_once __DIR__ . '/../ui/header.php';
?>

<!-- KOLTUK SAYFASI — tamamen beyaz, inline stiller garantili görünüm -->
<style>
  body { background: #f0f4fa; }

  .seat-page { max-width: 860px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }

  /* Steps */
  .sp-steps { display:flex; justify-content:center; align-items:flex-start; gap:0; margin-bottom:2rem; }
  .sp-step  { display:flex; flex-direction:column; align-items:center; gap:4px; }
  .sp-dot   {
    width:34px; height:34px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:0.82rem; font-weight:700;
    border:2px solid #ccd5e8; color:#aaa; background:#fff;
    transition: all .2s;
  }
  .sp-dot.done   { background:#00b894; border-color:#00b894; color:#fff; }
  .sp-dot.active { background:#e8a020; border-color:#e8a020; color:#fff; }
  .sp-label { font-size:0.68rem; color:#aaa; font-family:'DM Sans',sans-serif; }
  .sp-line  { width:60px; height:2px; background:#dde3ef; margin-top:16px; }
  .sp-line.done { background:#00b894; }

  /* Özet kutusu */
  .sp-summary {
    background:#fff; border:2px solid #e0e8f5; border-radius:14px;
    padding:1.2rem 1.5rem; display:flex; justify-content:space-between;
    align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:2rem;
    box-shadow:0 2px 10px rgba(10,21,53,0.07);
  }
  .sp-route { font-size:1.15rem; font-weight:700; color:#0a1535; }
  .sp-meta  { font-size:0.82rem; color:#888; margin-top:2px; }
  .sp-price { font-size:1.8rem; font-weight:700; color:#e8a020; }

  .sp-title    { font-family:'Playfair Display',serif; font-size:1.6rem; color:#0a1535; margin-bottom:.3rem; }
  .sp-subtitle { color:#888; font-size:.9rem; margin-bottom:1.5rem; }

  /* Uçak gövdesi */
  .aircraft {
    background:#fff; border:2px solid #e0e8f5; border-radius:24px;
    padding:2rem 1.5rem; box-shadow:0 4px 20px rgba(10,21,53,0.08);
    max-width:600px; margin:0 auto;
  }
  .aircraft-top { text-align:center; margin-bottom:1rem; }
  .aircraft-top span { font-size:2.5rem; opacity:.3; }

  /* Legend */
  .legend {
    display:flex; justify-content:center; gap:20px;
    margin-bottom:1.5rem; flex-wrap:wrap;
  }
  .legend-item { display:flex; align-items:center; gap:7px; font-size:.82rem; color:#555; font-family:'DM Sans',sans-serif; }
  .legend-box  { width:22px; height:22px; border-radius:5px; border:2px solid; }
  .legend-box.avail    { background:#eef2ff; border-color:#c0cfe8; }
  .legend-box.sel      { background:#e8a020; border-color:#c8840a; }
  .legend-box.occ      { background:#fde8e8; border-color:#e53e3e; }

  /* Sınıf ayırıcı */
  .class-divider {
    text-align:center; padding:6px 0; margin:10px 0;
    border-top:1px dashed #dde3ef; border-bottom:1px dashed #dde3ef;
    font-size:.72rem; color:#aaa; letter-spacing:2px;
    text-transform:uppercase; font-family:'DM Sans',sans-serif;
  }

  /* Koltuk satırı */
  .srow { display:flex; align-items:center; justify-content:center; gap:5px; margin-bottom:5px; }
  .srow-num { width:22px; text-align:center; font-size:.68rem; color:#bbb; font-family:'DM Sans',sans-serif; }
  .saisle   { width:18px; }

  /* KOLTUK */
  .seat-btn {
    width:38px; height:38px;
    border-radius:7px 7px 4px 4px;
    border:2px solid #c0cfe8;
    background:#eef2ff;
    color:#5a7ab0;
    font-size:.72rem; font-weight:700;
    font-family:'DM Sans',sans-serif;
    cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:all .15s ease;
    user-select:none;
  }
  .seat-btn:hover:not(.occ) {
    background:#fff3d0;
    border-color:#e8a020;
    color:#b87800;
    transform:scale(1.08);
  }
  .seat-btn.sel {
    background:#e8a020 !important;
    border-color:#c8840a !important;
    color:#fff !important;
    transform:scale(1.08);
    box-shadow:0 3px 10px rgba(232,160,32,.4);
  }
  .seat-btn.occ {
    background:#fde8e8 !important;
    border-color:#e53e3e !important;
    color:#e53e3e !important;
    cursor:not-allowed !important;
    opacity:.85;
  }

  /* Seçilen koltuk bilgisi */
  .selected-info {
    max-width:600px; margin:1.2rem auto 0;
    background:#fffbf0; border:2px solid #e8a020; border-radius:10px;
    padding:.9rem 1.2rem; text-align:center;
    font-family:'DM Sans',sans-serif; color:#555;
    display:none;
  }
  .selected-info strong { color:#e8a020; font-size:1.2rem; }

  /* Devam butonu */
  .sp-btn-wrap { max-width:600px; margin:1.5rem auto 0; }
  .sp-continue {
    width:100%; padding:15px; border-radius:10px; border:none; cursor:pointer;
    background:linear-gradient(135deg,#e8a020,#f0c040);
    color:#111; font-size:1.05rem; font-weight:700;
    font-family:'DM Sans',sans-serif; transition:all .2s;
  }
  .sp-continue:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 6px 20px rgba(232,160,32,.4); }
  .sp-continue:disabled { opacity:.45; cursor:not-allowed; }

  .sp-back {
    display:inline-flex; align-items:center; gap:6px;
    padding:9px 18px; border-radius:8px;
    border:1.5px solid #ccd5e8; background:#fff;
    color:#0a1535; font-family:'DM Sans',sans-serif;
    font-size:.9rem; text-decoration:none; margin-bottom:1.5rem;
    transition:all .18s;
  }
  .sp-back:hover { border-color:#e8a020; color:#e8a020; }
</style>

<div class="seat-page">

  <a href="javascript:history.back()" class="sp-back">← Uçuşlara Dön</a>

  <!-- ADIMLAR -->
  <div class="sp-steps">
    <div class="sp-step"><div class="sp-dot done">✓</div><span class="sp-label">Uçuş Seç</span></div>
    <div class="sp-line done"></div>
    <div class="sp-step"><div class="sp-dot active">2</div><span class="sp-label">Koltuk</span></div>
    <div class="sp-line"></div>
    <div class="sp-step"><div class="sp-dot">3</div><span class="sp-label">Yolcu</span></div>
    <div class="sp-line"></div>
    <div class="sp-step"><div class="sp-dot">4</div><span class="sp-label">Ödeme</span></div>
    <div class="sp-line"></div>
    <div class="sp-step"><div class="sp-dot">5</div><span class="sp-label">Bilet</span></div>
  </div>

  <!-- ÖZET -->
  <div class="sp-summary">
    <div>
      <div class="sp-route"><?= htmlspecialchars($ucus['kalkis_sehir']) ?> → <?= htmlspecialchars($ucus['varis_sehir']) ?></div>
      <div class="sp-meta">
        <?= htmlspecialchars($ucus['firma'] ?? '') ?> ·
        <?= htmlspecialchars($ucus['ucus_no'] ?? '') ?> ·
        <?= substr($ucus['kalkis_zamani'],0,5) ?> → <?= substr($ucus['varis_zamani'],0,5) ?> ·
        <?= date('d.m.Y', strtotime($gidis)) ?>
      </div>
    </div>
    <div class="sp-price">₺<?= number_format($ucus['fiyat'],0,'.','.') ?></div>
  </div>

  <h1 class="sp-title">Koltuk Seçin</h1>
  <p class="sp-subtitle">Bir koltuğa tıklayın. 🔴 Kırmızı = Dolu &nbsp;|&nbsp; 🟡 Sarı = Seçiminiz</p>

  <!-- LEGEND -->
  <div class="legend">
    <div class="legend-item"><div class="legend-box avail"></div> Boş</div>
    <div class="legend-item"><div class="legend-box sel"></div> Seçili</div>
    <div class="legend-item"><div class="legend-box occ"></div> Dolu</div>
  </div>

  <!-- UÇAK GÖVDESİ -->
  <div class="aircraft">
    <div class="aircraft-top"><span>✈</span></div>

    <?php
    $cols = ['A','B','C','D','E','F'];
    for ($row = 1; $row <= 30; $row++):

      if ($row === 1): ?>
        <div class="class-divider">✦ Business Class — Sıra 1–5 ✦</div>
      <?php endif;
      if ($row === 6): ?>
        <div class="class-divider">✦ Ekonomi Sınıfı — Sıra 6–30 ✦</div>
      <?php endif; ?>

      <div class="srow">
        <div class="srow-num"><?= $row ?></div>
        <?php foreach ($cols as $idx => $col):
          $sid       = "$row$col";
          $isDolu    = in_array($sid, $doluKoltuklar);
          $extraCls  = $isDolu ? 'occ' : '';
          $clickAttr = $isDolu ? '' : "onclick=\"seciKoltuk('$sid')\"";
          if ($idx === 3) echo '<div class="saisle"></div>';
        ?>
          <div class="seat-btn <?= $extraCls ?>"
               id="s-<?= $sid ?>"
               <?= $clickAttr ?>
               title="<?= $sid ?>">
            <?= $col ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endfor; ?>
  </div>

  <!-- SEÇİLEN KOLTUK BİLGİSİ -->
  <div class="selected-info" id="secilenBilgi">
    Seçtiğiniz koltuk: <strong id="secilenGoster">—</strong>
  </div>

  <!-- DEVAM FORMU -->
  <form action="passenger.php" method="GET" id="koltukForm" class="sp-btn-wrap">
    <input type="hidden" name="ucus_id"      value="<?= htmlspecialchars($ucus_id) ?>">
    <input type="hidden" name="gidis_tarihi"  value="<?= htmlspecialchars($gidis) ?>">
    <input type="hidden" name="donus_tarihi"  value="<?= htmlspecialchars($donus) ?>">
    <input type="hidden" name="ucus_json"    value="<?= htmlspecialchars($ucus_json) ?>">
    <input type="hidden" name="koltuk_no"    id="koltukNo" value="">
    <button type="submit" class="sp-continue" id="btnDevam" disabled onclick="return kontrol()">
      Devam Et — Yolcu Bilgileri →
    </button>
  </form>

</div>

<script>
let secilen = '';
const dolu = <?= json_encode(array_values($doluKoltuklar)) ?>;

function seciKoltuk(id) {
  // Öncekini sıfırla
  if (secilen) {
    const prev = document.getElementById('s-' + secilen);
    if (prev) prev.classList.remove('sel');
  }
  secilen = id;
  const el = document.getElementById('s-' + id);
  if (el) el.classList.add('sel');

  document.getElementById('secilenGoster').textContent = id;
  document.getElementById('secilenBilgi').style.display = 'block';
  document.getElementById('koltukNo').value = id;
  document.getElementById('btnDevam').disabled = false;
}

function kontrol() {
  if (!secilen) { alert('Lütfen bir koltuk seçin!'); return false; }
  return true;
}
</script>

<?php require_once __DIR__ . '/../ui/footer.php'; ?>
