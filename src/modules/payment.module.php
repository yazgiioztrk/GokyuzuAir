<?php
// payment.php — Ödeme Sayfası
require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../services/flight_service.php';
$pageTitle = 'Ödeme — Gökyüzü Air';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /GokyuzuAir/index.php'); exit; }

$_SESSION['booking'] = [
    'ucus_id'      => (int)$_POST['ucus_id'],
    'gidis_tarihi' => $_POST['gidis_tarihi'] ?? '',
    'donus_tarihi' => $_POST['donus_tarihi'] ?? '',
    'koltuk_no'    => $_POST['koltuk_no'] ?? '',
    'ucus_json'    => $_POST['ucus_json'] ?? '',
    'ad'           => trim($_POST['ad'] ?? ''),
    'soyad'        => trim($_POST['soyad'] ?? ''),
    'email'        => trim($_POST['email'] ?? ''),
    'tel'          => trim($_POST['tel'] ?? ''),
    'cinsiyet'     => $_POST['cinsiyet'] ?? 'Erkek',
    'tc_no'        => trim($_POST['tc_no'] ?? ''),
    'fiyat'        => (float)($_POST['fiyat'] ?? 0),
    'sinif'        => $_POST['sinif'] ?? 'Ekonomi',
];

$b = $_SESSION['booking'];
$ucus = null;
if ($b['ucus_id'] > 0) $ucus = getFlightById($b['ucus_id']);
if (!$ucus && $b['ucus_json']) $ucus = json_decode($b['ucus_json'], true);
if (!$ucus) { header('Location: /GokyuzuAir/index.php'); exit; }

require_once __DIR__ . '/../ui/header.php';
?>

<style>
body { background: #f0f4fa; }

.pay-page { max-width: 1060px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }

/* Steps */
.pp-steps { display:flex; justify-content:center; align-items:flex-start; gap:0; margin-bottom:2rem; }
.pp-step  { display:flex; flex-direction:column; align-items:center; gap:4px; }
.pp-dot   { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.82rem; font-weight:700; border:2px solid #ccd5e8; color:#aaa; background:#fff; }
.pp-dot.done   { background:#00b894; border-color:#00b894; color:#fff; }
.pp-dot.active { background:#e8a020; border-color:#e8a020; color:#fff; }
.pp-label { font-size:.68rem; color:#aaa; font-family:'DM Sans',sans-serif; }
.pp-line  { width:60px; height:2px; background:#dde3ef; margin-top:16px; }
.pp-line.done { background:#00b894; }

/* Özet */
.pp-summary {
  background:#fff; border:2px solid #e0e8f5; border-radius:14px;
  padding:1.2rem 1.5rem; display:flex; justify-content:space-between;
  align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:2rem;
  box-shadow:0 2px 10px rgba(10,21,53,.07);
}
.pp-route { font-size:1.15rem; font-weight:700; color:#0a1535; }
.pp-meta  { font-size:.82rem; color:#888; margin-top:2px; }
.pp-price { font-size:1.8rem; font-weight:700; color:#e8a020; }

/* İki kolon düzen */
.pay-grid { display:grid; grid-template-columns:1fr 380px; gap:2rem; align-items:start; }
@media(max-width:820px){ .pay-grid{ grid-template-columns:1fr; } }

/* KART ÖNİZLEME */
.card-preview-wrap { perspective:1000px; margin-bottom:2rem; }
.card-preview {
  width:100%; max-width:380px; height:220px;
  border-radius:18px; position:relative;
  transform-style:preserve-3d;
  transition:transform .7s cubic-bezier(.4,0,.2,1);
  cursor:pointer;
}
.card-preview.flipped { transform:rotateY(180deg); }

.card-front, .card-back {
  position:absolute; inset:0;
  border-radius:18px;
  backface-visibility:hidden;
  -webkit-backface-visibility:hidden;
  padding:1.5rem;
  display:flex; flex-direction:column; justify-content:space-between;
  box-shadow:0 12px 40px rgba(10,21,53,.3);
}
.card-front {
  background:linear-gradient(135deg,#0a1535 0%,#1a3a6e 60%,#2a5aa0 100%);
  color:#fff;
}
.card-back {
  background:linear-gradient(135deg,#1a3a6e 0%,#0a1535 100%);
  transform:rotateY(180deg);
  color:#fff;
}

/* Kart ön yüz elemanları */
.card-chip {
  width:44px; height:34px; border-radius:6px;
  background:linear-gradient(135deg,#d4a843,#f0c040);
  position:relative; overflow:hidden;
}
.card-chip::before {
  content:''; position:absolute; inset:0;
  background:repeating-linear-gradient(90deg,transparent,transparent 8px,rgba(0,0,0,.15) 8px,rgba(0,0,0,.15) 9px);
}
.card-chip::after {
  content:''; position:absolute; top:50%; left:0; right:0; height:1px;
  background:rgba(0,0,0,.2); transform:translateY(-50%);
}
.card-logo-top { display:flex; justify-content:space-between; align-items:center; }
.card-brand { font-family:'Playfair Display',serif; font-size:.9rem; color:rgba(255,255,255,.6); }
.card-network { font-size:1.6rem; }

.card-number-display {
  font-size:1.35rem; letter-spacing:4px; font-weight:600;
  font-family:monospace; color:#fff; text-shadow:0 1px 4px rgba(0,0,0,.3);
}
.card-bottom { display:flex; justify-content:space-between; align-items:flex-end; }
.card-label { font-size:.58rem; color:rgba(255,255,255,.5); text-transform:uppercase; letter-spacing:1px; margin-bottom:3px; }
.card-value { font-size:.92rem; font-weight:600; color:#fff; letter-spacing:1px; }

/* Kart arka yüz */
.card-mag { height:44px; background:#222; margin:-1.5rem -1.5rem 1rem; border-radius:18px 18px 0 0; }
.card-sig-strip {
  background:linear-gradient(90deg,#f5f5f5,#e0e0e0);
  border-radius:4px; padding:.4rem .8rem;
  display:flex; justify-content:space-between; align-items:center;
}
.card-sig-lines { flex:1; }
.card-sig-line { height:2px; background:#bbb; margin:3px 0; border-radius:1px; }
.card-cvv-box {
  background:#fff; border-radius:4px; padding:.3rem .7rem;
  font-size:1rem; font-weight:700; color:#0a1535; letter-spacing:3px;
  min-width:54px; text-align:center; font-family:monospace;
}
.card-back-bottom { display:flex; justify-content:space-between; align-items:center; }
.card-back-logo { font-family:'Playfair Display',serif; font-size:.85rem; color:rgba(255,255,255,.5); }
.card-back-logo em { color:#e8a020; font-style:normal; }
.card-hologram {
  width:38px; height:38px; border-radius:50%;
  background:conic-gradient(#e8a020,#00b894,#3b82f6,#e8a020);
  opacity:.7;
}

/* Form bölümü */
.pay-form-box {
  background:#fff; border:1.5px solid #e0e8f5; border-radius:16px;
  padding:2rem; box-shadow:0 2px 12px rgba(10,21,53,.06);
}
.pay-form-box h2 {
  font-family:'Playfair Display',serif; font-size:1.2rem;
  color:#0a1535; margin-bottom:1.5rem;
  padding-bottom:.8rem; border-bottom:2px solid #eef2f9;
}
.pp-label-txt { font-size:.75rem; font-weight:700; color:#666; text-transform:uppercase; letter-spacing:.6px; display:block; margin-bottom:6px; }
.pp-input {
  width:100%; padding:12px 14px; border-radius:8px;
  border:1.5px solid #d0daea; background:#f7f9ff;
  color:#0a1535; font-size:.95rem; font-family:'DM Sans',sans-serif;
  outline:none; transition:all .18s;
}
.pp-input:focus { border-color:#e8a020; background:#fffdf5; }
.pp-input::placeholder { color:#bbb; }
.pp-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem; }
.pp-full { margin-bottom:1rem; }

/* Özet kutu (sağ) */
.pp-summary-box {
  background:#fff; border:1.5px solid #e0e8f5; border-radius:16px;
  padding:1.8rem; box-shadow:0 2px 12px rgba(10,21,53,.06);
  position:sticky; top:80px;
}
.pp-summary-box h3 { font-family:'Playfair Display',serif; font-size:1.1rem; color:#0a1535; margin-bottom:1.2rem; }
.pp-sum-row { display:flex; justify-content:space-between; align-items:center; padding:9px 0; border-bottom:1px solid #f0f4fa; font-size:.88rem; }
.pp-sum-row:last-child { border:none; }
.pp-sum-label { color:#888; }
.pp-sum-val   { color:#0a1535; font-weight:600; }
.pp-sum-total { font-size:1.15rem !important; font-weight:700 !important; color:#e8a020 !important; }

/* Submit butonu */
.pp-submit {
  width:100%; padding:15px; border-radius:10px; border:none; cursor:pointer;
  background:linear-gradient(135deg,#e8a020,#f0c040);
  color:#111; font-size:1.05rem; font-weight:700;
  font-family:'DM Sans',sans-serif; margin-top:1.2rem; transition:all .2s;
}
.pp-submit:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(232,160,32,.4); }

.pp-back {
  display:inline-flex; align-items:center; gap:6px;
  padding:9px 18px; border-radius:8px; border:1.5px solid #ccd5e8;
  background:#fff; color:#0a1535; font-family:'DM Sans',sans-serif;
  font-size:.9rem; text-decoration:none; margin-bottom:1.5rem; transition:all .18s;
}
.pp-back:hover { border-color:#e8a020; color:#e8a020; }
.pp-secure { text-align:center; font-size:.75rem; color:#aaa; margin-top:.8rem; }

/* Kart tipi ikonları */
.card-type-icons { display:flex; gap:8px; margin-bottom:1rem; }
.card-type-btn {
  padding:6px 14px; border-radius:6px; border:2px solid #e0e8f5;
  background:#f7f9ff; cursor:pointer; font-size:.75rem; font-weight:700;
  color:#888; transition:all .18s;
}
.card-type-btn.active { border-color:#e8a020; background:#fffbf0; color:#b87800; }
</style>

<div class="pay-page">

  <a href="javascript:history.back()" class="pp-back">← Yolcu Bilgilerine Dön</a>

  <!-- ADIMLAR -->
  <div class="pp-steps">
    <div class="pp-step"><div class="pp-dot done">✓</div><span class="pp-label">Uçuş Seç</span></div>
    <div class="pp-line done"></div>
    <div class="pp-step"><div class="pp-dot done">✓</div><span class="pp-label">Koltuk</span></div>
    <div class="pp-line done"></div>
    <div class="pp-step"><div class="pp-dot done">✓</div><span class="pp-label">Yolcu</span></div>
    <div class="pp-line done"></div>
    <div class="pp-step"><div class="pp-dot active">4</div><span class="pp-label">Ödeme</span></div>
    <div class="pp-line"></div>
    <div class="pp-step"><div class="pp-dot">5</div><span class="pp-label">Bilet</span></div>
  </div>

  <!-- ÖZET ÜST -->
  <div class="pp-summary">
    <div>
      <div class="pp-route"><?= htmlspecialchars($ucus['kalkis_sehir']) ?> → <?= htmlspecialchars($ucus['varis_sehir']) ?></div>
      <div class="pp-meta">
        <?= htmlspecialchars($b['ad'].' '.$b['soyad']) ?> · Koltuk <strong style="color:#e8a020"><?= htmlspecialchars($b['koltuk_no']) ?></strong>
        · <?= date('d.m.Y', strtotime($b['gidis_tarihi'])) ?>
      </div>
    </div>
    <div class="pp-price">₺<?= number_format($b['fiyat'],0,'.','.') ?></div>
  </div>

  <div class="pay-grid">
    <!-- SOL: KART + FORM -->
    <div>
      <!-- İNTERAKTİF KART ÖNİZLEME -->
      <div class="card-preview-wrap">
        <div class="card-preview" id="cardPreview">

          <!-- KART ÖN YÜZ -->
          <div class="card-front">
            <div class="card-logo-top">
              <div class="card-chip"></div>
              <div>
                <div class="card-brand" id="cardBrandDisplay">Gökyüzü Air</div>
                <div class="card-network" id="cardNetworkIcon">💳</div>
              </div>
            </div>
            <div class="card-number-display" id="cardNumberDisplay">
              •••• &nbsp;•••• &nbsp;•••• &nbsp;••••
            </div>
            <div class="card-bottom">
              <div>
                <div class="card-label">Kart Sahibi</div>
                <div class="card-value" id="cardNameDisplay"><?= strtoupper(htmlspecialchars($b['ad'].' '.$b['soyad'])) ?></div>
              </div>
              <div>
                <div class="card-label">Son Kullanma</div>
                <div class="card-value" id="cardExpDisplay">MM/YY</div>
              </div>
            </div>
          </div>

          <!-- KART ARKA YÜZ -->
          <div class="card-back">
            <div class="card-mag"></div>
            <div class="card-sig-strip">
              <div class="card-sig-lines">
                <div class="card-sig-line"></div>
                <div class="card-sig-line"></div>
                <div class="card-sig-line"></div>
              </div>
              <div class="card-cvv-box" id="cvvDisplay">•••</div>
            </div>
            <div class="card-back-bottom">
              <div class="card-back-logo">Gökyüzü<em>Air</em></div>
              <div class="card-hologram"></div>
            </div>
          </div>

        </div>
      </div>

      <!-- KART TİPİ SEÇİMİ -->
      <div class="card-type-icons">
        <button type="button" class="card-type-btn active" onclick="setCardType(this,'VISA','💳')">VISA</button>
        <button type="button" class="card-type-btn" onclick="setCardType(this,'MasterCard','🔴')">Mastercard</button>
        <button type="button" class="card-type-btn" onclick="setCardType(this,'Troy','🇹🇷')">Troy</button>
        <button type="button" class="card-type-btn" onclick="setCardType(this,'Amex','⬛')">Amex</button>
      </div>

      <!-- FORM -->
      <form action="ticket.php" method="POST" id="payForm">
        <div class="pay-form-box">
          <h2>💳 Kart Bilgileri</h2>

          <div class="pp-full">
            <label class="pp-label-txt">Kart Üzerindeki İsim</label>
            <input class="pp-input" type="text" name="kart_isim"
              placeholder="AD SOYAD" required
              style="text-transform:uppercase"
              value="<?= strtoupper(htmlspecialchars($b['ad'].' '.$b['soyad'])) ?>"
              oninput="document.getElementById('cardNameDisplay').textContent=this.value.toUpperCase()||'AD SOYAD'">
          </div>

          <div class="pp-full">
            <label class="pp-label-txt">Kart Numarası</label>
            <input class="pp-input" type="text" name="kart_no" id="kart_no"
              placeholder="0000 0000 0000 0000" maxlength="19" required
              oninput="formatCard(this)">
          </div>

          <div class="pp-row">
            <div>
              <label class="pp-label-txt">Son Kullanma Tarihi</label>
              <input class="pp-input" type="text" name="son_tarih" id="exp_date"
                placeholder="MM/YY" maxlength="5" required
                oninput="formatExp(this)"
                onfocus="document.getElementById('cardPreview').classList.remove('flipped')">
            </div>
            <div>
              <label class="pp-label-txt">CVV / CVC</label>
              <input class="pp-input" type="password" name="cvv" id="cvv_input"
                placeholder="•••" maxlength="4" required
                onfocus="document.getElementById('cardPreview').classList.add('flipped')"
                onblur="document.getElementById('cardPreview').classList.remove('flipped')"
                oninput="document.getElementById('cvvDisplay').textContent=this.value||'•••'">
            </div>
          </div>
        </div>

        <!-- GİZLİ ALANLAR -->
        <input type="hidden" name="ucus_id"      value="<?= $b['ucus_id'] ?>">
        <input type="hidden" name="ucus_json"    value="<?= htmlspecialchars($b['ucus_json']) ?>">
        <input type="hidden" name="gidis_tarihi"  value="<?= htmlspecialchars($b['gidis_tarihi']) ?>">
        <input type="hidden" name="donus_tarihi"  value="<?= htmlspecialchars($b['donus_tarihi']) ?>">
        <input type="hidden" name="koltuk_no"    value="<?= htmlspecialchars($b['koltuk_no']) ?>">
        <input type="hidden" name="ad"           value="<?= htmlspecialchars($b['ad']) ?>">
        <input type="hidden" name="soyad"        value="<?= htmlspecialchars($b['soyad']) ?>">
        <input type="hidden" name="email"        value="<?= htmlspecialchars($b['email']) ?>">
        <input type="hidden" name="tel"          value="<?= htmlspecialchars($b['tel']) ?>">
        <input type="hidden" name="cinsiyet"     value="<?= htmlspecialchars($b['cinsiyet']) ?>">
        <input type="hidden" name="fiyat"        value="<?= $b['fiyat'] ?>">
        <input type="hidden" name="sinif"        value="<?= htmlspecialchars($b['sinif']) ?>">

        <button type="submit" class="pp-submit">✔ Ödemeyi Tamamla &amp; Bilet Al</button>
        <p class="pp-secure">🔒 256-bit SSL şifrelemeli güvenli ödeme</p>
      </form>
    </div>

    <!-- SAĞ: REZERVASYON ÖZETİ -->
    <div class="pp-summary-box">
      <h3>📋 Rezervasyon Özeti</h3>
      <div class="pp-sum-row"><span class="pp-sum-label">Havayolu</span><span class="pp-sum-val"><?= htmlspecialchars($ucus['firma'] ?? '') ?></span></div>
      <div class="pp-sum-row"><span class="pp-sum-label">Uçuş No</span><span class="pp-sum-val"><?= htmlspecialchars($ucus['ucus_no'] ?? '') ?></span></div>
      <div class="pp-sum-row"><span class="pp-sum-label">Güzergah</span><span class="pp-sum-val"><?= htmlspecialchars($ucus['kalkis_sehir']) ?> → <?= htmlspecialchars($ucus['varis_sehir']) ?></span></div>
      <div class="pp-sum-row"><span class="pp-sum-label">Tarih</span><span class="pp-sum-val"><?= date('d.m.Y', strtotime($b['gidis_tarihi'])) ?></span></div>
      <div class="pp-sum-row"><span class="pp-sum-label">Saat</span><span class="pp-sum-val"><?= substr($ucus['kalkis_zamani']??'',0,5) ?> → <?= substr($ucus['varis_zamani']??'',0,5) ?></span></div>
      <div class="pp-sum-row"><span class="pp-sum-label">Koltuk</span><span class="pp-sum-val" style="color:#e8a020;font-weight:700"><?= htmlspecialchars($b['koltuk_no']) ?></span></div>
      <div class="pp-sum-row"><span class="pp-sum-label">Sınıf</span><span class="pp-sum-val"><?= htmlspecialchars($b['sinif']) ?></span></div>
      <div class="pp-sum-row"><span class="pp-sum-label">Yolcu</span><span class="pp-sum-val"><?= htmlspecialchars($b['ad'].' '.$b['soyad']) ?></span></div>
      <div class="pp-sum-row" style="margin-top:.5rem">
        <span class="pp-sum-label pp-sum-total">Toplam</span>
        <span class="pp-sum-val pp-sum-total">₺<?= number_format($b['fiyat'],0,'.','.') ?></span>
      </div>
    </div>
  </div>

</div>

<script>
// Kart numarası formatlama + önizleme
function formatCard(el) {
  let v = el.value.replace(/\D/g,'').substring(0,16);
  let fmt = v.replace(/(.{4})/g,'$1 ').trim();
  el.value = fmt;
  // Önizleme güncelle
  let disp = v.padEnd(16,'•');
  let parts = [disp.slice(0,4), disp.slice(4,8), disp.slice(8,12), disp.slice(12,16)];
  document.getElementById('cardNumberDisplay').innerHTML = parts.join(' &nbsp;');
  // Ağı otomatik algıla
  autoDetectNetwork(v);
}

function autoDetectNetwork(num) {
  const icon = document.getElementById('cardNetworkIcon');
  const brand = document.getElementById('cardBrandDisplay');
  if (num.startsWith('4')) { icon.textContent='💳'; brand.textContent='VISA'; }
  else if (num.startsWith('5')) { icon.textContent='🔴'; brand.textContent='Mastercard'; }
  else if (num.startsWith('9')) { icon.textContent='🇹🇷'; brand.textContent='Troy'; }
  else if (num.startsWith('3')) { icon.textContent='⬛'; brand.textContent='Amex'; }
}

function formatExp(el) {
  let v = el.value.replace(/\D/g,'');
  if (v.length >= 2) v = v.substring(0,2) + '/' + v.substring(2,4);
  el.value = v;
  document.getElementById('cardExpDisplay').textContent = v || 'MM/YY';
}

function setCardType(btn, name, icon) {
  document.querySelectorAll('.card-type-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('cardNetworkIcon').textContent = icon;
  document.getElementById('cardBrandDisplay').textContent = name;
}
</script>

<?php require_once __DIR__ . '/../ui/footer.php'; ?>
